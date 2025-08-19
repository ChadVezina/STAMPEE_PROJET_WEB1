<?php
declare(strict_types=1);

namespace App\UI;

final class Form
{
    /** Ouvre un form avec classes BEM & attributs */
    public static function open(array $opts = []): string
    {
        $action = htmlspecialchars((string)($opts['action'] ?? ''), ENT_QUOTES);
        $method = strtoupper((string)($opts['method'] ?? 'POST'));
        $enctype = (string)($opts['enctype'] ?? '');
        $class = 'form' . (!empty($opts['class']) ? ' ' . $opts['class'] : '');

        $attrs = '';
        foreach (($opts['attrs'] ?? []) as $k => $v) {
            $attrs .= ' ' . htmlspecialchars($k, ENT_QUOTES) . '="' . htmlspecialchars((string)$v, ENT_QUOTES) . '"';
        }
        return sprintf(
            '<form action="%s" method="%s"%s class="%s"%s>',
            $action,
            $method === 'GET' ? 'GET' : 'POST',
            $enctype ? ' enctype="' . htmlspecialchars($enctype, ENT_QUOTES) . '"' : '',
            $class,
            $attrs
        );
    }

    public static function close(): string
    {
        return '</form>';
    }

    /** Champ générique */
    public static function field(array $cfg): string
    {
        $type   = strtolower((string)($cfg['type'] ?? 'text'));
        $name   = (string)($cfg['name'] ?? '');
        $id     = (string)($cfg['id'] ?? $name);
        $label  = (string)($cfg['label'] ?? '');
        $value  = (string)($cfg['value'] ?? '');
        $hint   = (string)($cfg['hint'] ?? '');
        $error  = (string)($cfg['error'] ?? '');
        $required = !empty($cfg['required']);
        $disabled = !empty($cfg['disabled']);
        $readonly = !empty($cfg['readonly']);
        $placeholder = (string)($cfg['placeholder'] ?? '');
        $classWrap = 'field' . (!empty($cfg['wrapClass']) ? ' ' . $cfg['wrapClass'] : '');
        $classInput = 'input' . (!empty($cfg['inputClass']) ? ' ' . $cfg['inputClass'] : '');
        $pattern = (string)($cfg['pattern'] ?? '');
        $min = $cfg['min'] ?? null;
        $max = $cfg['max'] ?? null;
        $step = $cfg['step'] ?? null;

        $attr = [
            'id' => $id,
            'name' => $name,
            'class' => $classInput . ($error ? ' input--error' : ''),
            'placeholder' => $placeholder,
            'autocomplete' => (string)($cfg['autocomplete'] ?? 'on'),
            'aria-invalid' => $error ? 'true' : 'false'
        ];
        if ($required) $attr['required'] = 'required';
        if ($disabled) $attr['disabled'] = 'disabled';
        if ($readonly) $attr['readonly'] = 'readonly';
        if ($pattern)  $attr['pattern']  = $pattern;
        if ($min !== null)  $attr['min'] = (string)$min;
        if ($max !== null)  $attr['max'] = (string)$max;
        if ($step !== null) $attr['step'] = (string)$step;

        $attrStr = '';
        foreach ($attr as $k => $v) {
            $attrStr .= ' ' . $k . '="' . htmlspecialchars((string)$v, ENT_QUOTES) . '"';
        }

        $controls = '';
        switch ($type) {
            case 'textarea':
                $rows = (int)($cfg['rows'] ?? 4);
                $controls = sprintf('<textarea%s>%s</textarea>', $attrStr, htmlspecialchars($value, ENT_QUOTES));
                break;

            case 'select':
                $options = (array)($cfg['options'] ?? []);
                $controls = '<select' . $attrStr . '>';
                foreach ($options as $optValue => $optLabel) {
                    $sel = ((string)$optValue === (string)$value) ? ' selected' : '';
                    $controls .= '<option value="' . htmlspecialchars((string)$optValue, ENT_QUOTES) . '"' . $sel . '>'
                              . htmlspecialchars((string)$optLabel, ENT_QUOTES) . '</option>';
                }
                $controls .= '</select>';
                break;

            case 'checkbox':
                $checked = !empty($cfg['checked']) || (string)$value === '1';
                $controls = sprintf(
                    '<label class="check"><input type="checkbox" id="%s" name="%s" value="1"%s%s%s><span class="check__box"></span>%s</label>',
                    htmlspecialchars($id, ENT_QUOTES),
                    htmlspecialchars($name, ENT_QUOTES),
                    $checked ? ' checked' : '',
                    $required ? ' required' : '',
                    $disabled ? ' disabled' : '',
                    htmlspecialchars($label, ENT_QUOTES)
                );
                $label = ''; // label déjà rendu
                break;

            case 'password':
                $toggle = !empty($cfg['toggle']); // affiche bouton œil
                $attrStrPwd = $attrStr . ' data-password="input"';
                $controls = sprintf(
                    '<div class="input-password"><input type="password"%s value="%s">%s</div>',
                    $attrStrPwd,
                    htmlspecialchars($value, ENT_QUOTES),
                    $toggle ? '<button type="button" class="input-password__toggle" data-password="toggle" aria-label="Afficher le mot de passe">👁</button>' : ''
                );
                break;

            default:
                $controls = sprintf('<input type="%s"%s value="%s">', htmlspecialchars($type, ENT_QUOTES), $attrStr, htmlspecialchars($value, ENT_QUOTES));
                break;
        }

        return '<div class="' . $classWrap . '">' .
            ($label ? '<label class="field__label" for="' . htmlspecialchars($id, ENT_QUOTES) . '">' . htmlspecialchars($label, ENT_QUOTES) . ($required ? ' <span class="field__req">*</span>' : '') . '</label>' : '') .
            '<div class="field__control">' . $controls . '</div>' .
            ($hint ? '<p class="field__hint">' . htmlspecialchars($hint, ENT_QUOTES) . '</p>' : '') .
            ($error ? '<p class="field__error" role="alert">' . htmlspecialchars($error, ENT_QUOTES) . '</p>' : '') .
        '</div>';
    }

    /** Raccourcis typed */
    public static function input(array $cfg): string      { return self::field($cfg); }
    public static function textarea(array $cfg): string   { $cfg['type']='textarea'; return self::field($cfg); }
    public static function select(array $cfg): string     { $cfg['type']='select';   return self::field($cfg); }
    public static function checkbox(array $cfg): string   { $cfg['type']='checkbox'; return self::field($cfg); }
    public static function password(array $cfg): string   { $cfg['type']='password'; return self::field($cfg); }

    /** Rend à partir d’une config (genre Shadcn-like) */
    public static function fromConfig(array $fields): string
    {
        $html = '';
        foreach ($fields as $f) {
            $html .= self::field($f);
        }
        return $html;
    }

    /** Bouton(s) standards */
    public static function actions(array $opts = []): string
    {
        $submitLabel = (string)($opts['submit'] ?? 'Enregistrer');
        $cancelHref  = (string)($opts['cancel'] ?? '');
        $secondary   = '';
        if ($cancelHref) {
            $secondary = '<a class="button button--ghost form__btn" href="' . htmlspecialchars($cancelHref, ENT_QUOTES) . '">Annuler</a>';
        }
        return '<div class="form__actions">' .
                $secondary .
                '<button type="submit" class="button button--primary form__btn">' . htmlspecialchars($submitLabel, ENT_QUOTES) . '</button>' .
               '</div>';
    }
}
