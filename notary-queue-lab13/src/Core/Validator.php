<?php
declare(strict_types=1);

/** Санҷиши сервер. Методҳо $this бармегардонанд (занҷир); барои ҳар майдон танҳо хатои аввал нигоҳ дошта мешавад. */
final class Validator
{
    private array $errors = [];

    public function __construct(private array $input) {}

    private function value(string $field): string
    {
        return trim((string) ($this->input[$field] ?? ''));
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field] ??= $message;
        return $this;
    }

    public function required(string $field, string $label): self
    {
        if ($this->value($field) === '') {
            $this->addError($field, "Майдони «{$label}» ҳатмӣ аст");
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label): self
    {
        // mb_strlen: «ҳ» як аломат аст, ҳарчанд дар UTF-8 ду байт
        if (mb_strlen($this->value($field)) > $max) {
            $this->addError($field, "«{$label}»: на зиёда аз {$max} аломат");
        }
        return $this;
    }

    /** Қимат бояд аз рӯйхат бошад (холӣ — гузаронида мешавад, барои майдонҳои ихтиёрӣ) */
    public function in(string $field, array $allowed, string $label): self
    {
        $v = $this->value($field);
        if ($v !== '' && !in_array($v, array_map('strval', $allowed), true)) {
            $this->addError($field, "«{$label}»: қимати иҷозатнашуда");
        }
        return $this;
    }

    /** Адади бутун дар доираи [min, max] (холӣ — гузаронида мешавад) */
    public function integerBetween(string $field, int $min, int $max, string $label): self
    {
        $v = $this->value($field);
        if ($v === '') {
            return $this;
        }
        if (!preg_match('/^\d+$/', $v) || (int) $v < $min || (int) $v > $max) {
            $this->addError($field, "«{$label}»: адад аз {$min} то {$max}");
        }
        return $this;
    }

    public function errors(): array { return $this->errors; }
    public function fails(): bool   { return $this->errors !== []; }
}
