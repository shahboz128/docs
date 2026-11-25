// public/assets/js/app.js — интерактивии «Нотариат · Навбат» (лаб 11)
// Ҳамчун модули ES пайваст мешавад: <script type="module" src="../assets/js/app.js"></script>
// Модул тағйирёбандаҳои глобалӣ намесозад; ҳамаи маълумоти сервер танҳо тавассути textContent намоиш дода мешавад.

/* =====================================================================
   1. Утилитҳо
   ===================================================================== */

// Даъвати fn-ро то танаффуси вурудии корбар (delay мс) ба таъхир меандозад.
function debounce(fn, delay = 300) {
  let timerId;
  return (...args) => {
    clearTimeout(timerId);
    timerId = setTimeout(() => fn(...args), delay);
  };
}

// 'YYYY-MM-DD HH:MM:SS' -> 'HH:MM' (холӣ -> '')
function formatTime(dateTime) {
  if (!dateTime) return '';
  return dateTime.slice(11, 16);
}

// Санаи маҳаллӣ дар шакли YYYY-MM-DD ва вақти маҳаллӣ дар шакли HH:MM
const pad = (n) => String(n).padStart(2, '0');
function todayIso(now = new Date()) {
  return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
}
function nowHhmm(now = new Date()) {
  return `${pad(now.getHours())}:${pad(now.getMinutes())}`;
}

/* =====================================================================
   2. Валидатсияи шакли сабт (Constraint Validation API)
   ===================================================================== */

const FIELD_MESSAGES = {
  service_id: { valueMissing: 'Хизматро интихоб кунед.' },
  visit_date: { valueMissing: 'Санаи ташрифро нишон диҳед.', rangeUnderflow: 'Санаи гузашта интихоб карда намешавад.' },
  visit_time: { valueMissing: 'Вақти қабулро интихоб кунед.' },
  phone: { valueMissing: 'Рақами телефонро нависед.', patternMismatch: 'Рақамро дар шакли +992 90 000 00 11 нависед.' },
};

function messageFor(field) {
  const v = field.validity;
  if (v.valid) return '';
  if (v.customError) return field.validationMessage; // қоидаи худамон тавассути setCustomValidity()
  const custom = FIELD_MESSAGES[field.name] ?? {};
  if (v.valueMissing) return custom.valueMissing ?? 'Ин майдонро пур кунед.';
  if (v.rangeUnderflow) return custom.rangeUnderflow ?? 'Қиммат аз ҳадди ақал кам аст.';
  if (v.patternMismatch) return custom.patternMismatch ?? 'Шакли қимат нодуруст аст.';
  if (v.tooLong) return `На зиёда аз ${field.maxLength} аломат.`;
  if (v.typeMismatch || v.badInput) return 'Шакли қимат нодуруст аст.';
  return 'Қиматро санҷед.';
}

// Ошкор кардани ҳолати майдон: синфи Bootstrap is-invalid, aria-invalid ва матни хато
function showFieldState(field) {
  const message = messageFor(field);
  const feedback = document.getElementById(`${field.id}-error`);
  field.classList.toggle('is-invalid', message !== '');
  if (message) field.setAttribute('aria-invalid', 'true');
  else field.removeAttribute('aria-invalid');
  if (feedback) feedback.textContent = message;
  return message === '';
}

// Қоидаи ду майдон: агар сана — имрӯз бошад, вақт набояд гузашта бошад.
// Дар HTML5 чунин қоида нест, бинобар ин setCustomValidity истифода мешавад.
function checkVisitTime(form, now = new Date()) {
  const { visit_date: date, visit_time: time } = form.elements;
  time.setCustomValidity(''); // ҳатман барканор кардани хатои пештара, вагарна майдон ҳамеша «сурх» мемонад
  // <input type="date"> ҳамеша YYYY-MM-DD, <option value="HH:MM"> — онҳоро ҳамчун сатр муқоиса кардан мумкин аст
  if (date.value && time.value && date.value === todayIso(now) && time.value <= nowHhmm(now)) {
    time.setCustomValidity('Вақти интихобшуда аллакай гузаштааст.');
  }
}

function initBookingForm() {
  const form = document.getElementById('booking-form');
  if (!form) return; // дар ин саҳифа шакл нест

  const { visit_date: date, visit_time: time } = form.elements;
  const fields = () => form.querySelectorAll('input, select, textarea');
  date.min = todayIso(); // ишора барои тақвим + rangeUnderflow

  const refresh = (field) => {
    checkVisitTime(form);
    showFieldState(field);
    // тағйири сана метавонад вақтро дуруст/нодуруст кунад
    if (field === date && (time.classList.contains('is-invalid') || time.value)) showFieldState(time);
  };

  // focusout мегузарад (blur не) — як пешоманд барои тамоми шакл (делегатсия)
  form.addEventListener('focusout', (event) => {
    if (event.target.matches('input, select, textarea')) refresh(event.target);
  });
  form.addEventListener('input', (event) => {
    if (event.target.classList.contains('is-invalid')) refresh(event.target);
  });
  form.addEventListener('change', (event) => {
    if (event.target === date || event.target === time) refresh(event.target);
  });
  form.addEventListener('submit', (event) => {
    checkVisitTime(form);
    let firstInvalid = null;
    fields().forEach((field) => {
      if (!showFieldState(field) && !firstInvalid) firstInvalid = field;
    });
    if (firstInvalid) {
      event.preventDefault(); // шакли хатодорро намефиристем
      firstInvalid.focus();
    }
  });
}

/* =====================================================================
   3. Ҷустуҷӯи зинда (fetch + JSON + debounce + AbortController)
   ===================================================================== */

const STATUS_LABELS = {
  waiting: 'Интизорӣ', called: 'Даъватшуда', in_service: 'Дар хизмат',
  done: 'Анҷомёфта', no_show: 'Наомада', cancelled: 'Бекоршуда',
};
const TYPE_LABELS = { booked: 'Сабти пешакӣ', live: 'Бе сабт' };

function createCell(text, label = '', className = '') {
  const td = document.createElement('td');
  td.textContent = text ?? ''; // textContent: ҳар гуна < > & аз маълумот ҳамчун матн намоиш дода мешавад
  if (label) td.dataset.label = label;
  if (className) td.className = className;
  return td;
}

// urlTemplate: 'ticket.html?id={id}' (статикӣ, лаб 10–11) ё '/tickets/{id}' (барномаи PHP, лаб 13)
function createTicketRow(ticket, urlTemplate) {
  const tr = document.createElement('tr');

  const numberCell = document.createElement('td');
  numberCell.dataset.label = 'Талон';
  const link = document.createElement('a');
  link.className = 'fw-semibold';
  link.href = urlTemplate.replace('{id}', encodeURIComponent(ticket.id));
  link.textContent = ticket.ticket_label;
  numberCell.append(link);

  const statusCell = document.createElement('td');
  statusCell.dataset.label = 'Ҳолат';
  const badge = document.createElement('span');
  const status = STATUS_LABELS[ticket.status] ? ticket.status : 'waiting';
  badge.className = `badge status status-${status}`;
  badge.textContent = STATUS_LABELS[status]; // ҳолат бо МАТН, на танҳо бо ранг
  statusCell.append(badge);

  const actionsCell = document.createElement('td');
  actionsCell.className = 'text-end cell-actions';
  const open = document.createElement('a');
  open.className = 'btn btn-sm btn-outline-primary';
  open.href = link.href;
  open.textContent = 'Кушодан';
  actionsCell.append(open);
  if (ticket.status === 'waiting') {
    const cancel = document.createElement('button');
    cancel.type = 'button';
    cancel.className = 'btn btn-sm btn-outline-danger ms-1';
    cancel.textContent = 'Бекор кардан';
    cancel.dataset.cancelUrl = `/tickets/${encodeURIComponent(ticket.id)}/cancel`;
    cancel.dataset.ticketLabel = ticket.ticket_label;
    cancel.dataset.confirmAction = 'Бекор кардан';
    actionsCell.append(cancel);
  }

  tr.append(
    numberCell,
    createCell(ticket.service, 'Хизмат'),
    createCell(TYPE_LABELS[ticket.ticket_type] ?? '', 'Намуд'),
    createCell(ticket.client || '—', 'Мизоҷ'),
    createCell(formatTime(ticket.created_at), 'Вақти сабт'),
    statusCell,
    actionsCell,
  );
  return tr;
}

function renderTickets(tbody, tickets, urlTemplate) {
  if (tickets.length === 0) {
    const tr = document.createElement('tr');
    const td = createCell('Талонҳо ёфт нашуданд. Ҷустуҷӯро тағйир диҳед.');
    td.colSpan = 7;
    td.className = 'text-center text-muted py-4';
    tr.append(td);
    tbody.replaceChildren(tr);
    return;
  }
  tbody.replaceChildren(...tickets.map((t) => createTicketRow(t, urlTemplate)));
}

function initTicketSearch() {
  const input = document.getElementById('ticket-search');
  const tbody = document.getElementById('tickets-tbody');
  if (!input || !tbody) return;

  const urlTemplate = tbody.dataset.ticketUrl ?? 'ticket.html?id={id}';
  const statusBox = document.getElementById('ticket-search-status');
  const setStatus = (text) => { if (statusBox) statusBox.textContent = text; };
  let controller = null; // AbortController-и дархости ҷорӣ

  async function search(query) {
    controller?.abort(); // дархости пештараро, агар ба охир нарасида бошад, бекор мекунем
    controller = new AbortController();
    tbody.setAttribute('aria-busy', 'true');
    setStatus('Ҷустуҷӯ…');
    try {
      const response = await fetch(`/api/tickets?q=${encodeURIComponent(query)}`, {
        headers: { Accept: 'application/json' },
        signal: controller.signal,
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`); // fetch ба 404/500 хато намепартояд
      const tickets = await response.json();
      renderTickets(tbody, tickets, urlTemplate);
      setStatus(tickets.length ? `Талонҳо ёфт шуданд: ${tickets.length}` : 'Талонҳо ёфт нашуданд.');
    } catch (error) {
      if (error.name === 'AbortError') return; // дархост бо навтараш иваз шуд — муқаррарӣ
      console.error('Хатои ҷустуҷӯи зинда:', error);
      setStatus('Ҷустуҷӯ иҷро нашуд. Пайвастшавиро санҷед ва боз кӯшиш кунед.');
    } finally {
      tbody.removeAttribute('aria-busy');
    }
  }

  const debouncedSearch = debounce(search, 300);
  input.addEventListener('input', () => debouncedSearch(input.value.trim()));
}

/* =====================================================================
   4. Тасдиқи амали хатарнок (тирезаи модалии Bootstrap)
   ===================================================================== */

function initCancelConfirm() {
  const modalElement = document.getElementById('confirm-cancel-modal');
  if (!modalElement || !window.bootstrap) return;

  const form = document.getElementById('confirm-cancel-form');
  const labelElement = document.getElementById('confirm-cancel-label');
  const actionElement = document.getElementById('confirm-action-name'); // ихтиёрӣ: дар саҳифаҳои статикӣ нест
  const submitButton = form.querySelector('[type="submit"]');
  const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
  let lastTrigger = null;

  // Як пешоманд дар document: ба тугмаҳои баъд аз ҷустуҷӯ иловашуда низ кор мекунад (делегатсия)
  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-cancel-url], [data-delete-url]');
    if (!button) return;
    lastTrigger = button;
    form.action = button.dataset.cancelUrl ?? button.dataset.deleteUrl; // ҳамеша POST-форма
    labelElement.textContent = button.dataset.ticketLabel ?? '';
    if (actionElement) actionElement.textContent = button.dataset.confirmAction ?? 'Бекор кардан';
    submitButton.disabled = false;
    modal.show();
  });
  // Баъд аз пӯшидан фокусро ба тугмаи аввала бармегардонем (дастрасӣ)
  modalElement.addEventListener('hidden.bs.modal', () => {
    if (lastTrigger?.isConnected) lastTrigger.focus();
  });
  form.addEventListener('submit', () => { submitButton.disabled = true; }); // ҳимоя аз пахши дуюм
}

/* =====================================================================
   5. Оғоз (модул баъд аз таҳлили HTML иҷро мешавад, DOMContentLoaded лозим нест)
   ===================================================================== */
initBookingForm();
initTicketSearch();
initCancelConfirm();
