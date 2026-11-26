<?php /** @var \App\Core\View $this */ ?>
<div class="modal fade" id="confirm-cancel-modal" tabindex="-1" aria-labelledby="confirm-cancel-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header">
      <h2 class="modal-title h3" id="confirm-cancel-title">Амалро тасдиқ кунед</h2>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Пӯшидан"></button>
    </div>
    <div class="modal-body">
      <p class="mb-0">Амал: <strong id="confirm-action-name">Бекор кардан</strong> — талони <strong id="confirm-cancel-label"></strong>. Амалро баргардонидан мумкин нест.</p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Не, нигоҳ доштан</button>
      <form id="confirm-cancel-form" method="post" action="#">
        <button type="submit" class="btn btn-danger">Тасдиқ</button>
      </form>
    </div>
  </div></div>
</div>
