<?php
/**
 * rep2expack - ���X�t�B���^�����OHTML�v�f�N���X
 */

// {{{ ResFilterElement

class ResFilterElement extends ResFilter
{
    // {{{ getHiddenFields()

    /**
     * �B���p�����[�^�v�f�𐶐�����
     *
     * @param string $host
     * @param string $bbs
     * @param string $key
     * @param boolean $xhtml
     */
    static public function getHiddenFields($host, $bbs, $key, $xhtml = false)
    {
        $slash = $xhtml ? ' /' : '';
        $host = p2h($host);
        $bbs = p2h($bbs);
        $key = p2h($key);
        return <<<EOF
<input type="hidden" name="host" value="{$host}"{$slash}>
<input type="hidden" name="bbs" value="{$bbs}"{$slash}>
<input type="hidden" name="key" value="{$key}"{$slash}>
<input type="hidden" name="ls" value="all"{$slash}>
<input type="hidden" name="offline" value="1"{$slash}>
EOF;
    }

    // }}}
    // {{{ getWordField()

    /**
     * �������[�h����͂���v�f�𐶐�����
     *
     * @param array $extra_attributes
     * @param string $id_suffix
     * @param boolean $xhtml
     */
    static public function getWordField(array $extra_attributes = null,
                                        $id_suffix = null, $xhtml = false)
    {
        $slash = $xhtml ? ' /' : '';
        $name = 'rf[word]';
        $id = 'rf_word';
        if ($id_suffix !== null) {
            $id .= p2h($id_suffix);
        }
        $word = parent::getWord('p2h');

        $html = "<input type=\"text\" id=\"{$id}\" name=\"rf[word]\" value=\"{$word}\"";
        if ($extra_attributes) {
            foreach ($extra_attributes as $key => $value) {
                $key = p2h($key);
                $value = p2h($value);
                $html .= " {$key}=\"{$value}\"";
            }
        }
        $html .= "{$slash}>";

        return $html;
    }

    // }}}
    // {{{ getFieldField()

    /**
     * �����Ώۃt�B�[���h��I������v�f�𐶐�����
     *
     * @param string $id_suffix
     * @param boolean $xhtml
     */
    static public function getFieldField($id_suffix = null, $xhtml = false)
    {
        $filter = parent::getFilter();
        $fields = parent::$_fields;
        $default = is_object($filter) ? $filter->field : self::FIELD_DEFAULT;
        $key = 'field';
        return self::_getSelectField($fields, $default, $key, $id_suffix, $xhtml);
    }

    // }}}
    // {{{ getMethodField()

    /**
     * �������@��I������v�f�𐶐�����
     *
     * @param string $id_suffix
     * @param boolean $xhtml
     */
    static public function getMethodField($id_suffix = null, $xhtml = false)
    {
        $filter = parent::getFilter();
        $fields = parent::$_methods;
        $default = is_object($filter) ? $filter->method : self::METHOD_DEFAULT;
        $key = 'method';
        return self::_getSelectField($fields, $default, $key, $id_suffix, $xhtml);
    }

    // }}}
    // {{{ getMatchField()

    /**
     * �������[�h�Ƀ}�b�`����/���Ȃ���I������v�f�𐶐�����
     *
     * @param string $id_suffix
     * @param boolean $xhtml
     */
    static public function getMatchField($id_suffix = null, $xhtml = false)
    {
        $filter = parent::getFilter();
        $fields = parent::$_matches;
        $default = is_object($filter) ? $filter->match : self::MATCH_DEFAULT;
        $key = 'match';
        return self::_getSelectField($fields, $default, $key, $id_suffix, $xhtml);
    }

    // }}}
    // {{{ getIncludeField()

    /**
     * �}�b�`���ʈȊO�ɕ\�����郌�X��I������v�f�𐶐�����
     *
     * @param string $id_suffix
     * @param boolean $xhtml
     */
    static public function getIncludeField($id_suffix = null, $xhtml = false)
    {
        $filter = parent::getFilter();
        $fields = parent::$_includes;
        $default = is_object($filter) ? $filter->include : self::INCLUDE_DEFAULT;
        $key = 'include';
        return self::_getSelectField($fields, $default, $key, $id_suffix, $xhtml);
    }

    // }}}
    // {{{ _getSelectField()

    /**
     * select�v�f�𐶐�����
     *
     * @param array $fields
     * @param string $default
     * @param string $key
     * @param string $id_suffix
     * @param boolean $xhtml
     */
    static private function _getSelectField(array $fields, $default, $key,
                                            $id_suffix = null, $xhtml = false)
    {
        $name = "rf[{$key}]";
        $id = "rf_{$key}";
        if ($id_suffix !== null) {
            $id .= $id_suffix;
        }
        $name = p2h($name);
        $id = p2h($id);

        $html = "<select class=\"form-control\" style=\"width: auto;display: inline-block;\" id=\"{$id}\" name=\"{$name}\">";
        foreach ($fields as $value => $label) {
            if ($value == $default) {
                if ($xhtml) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = ' selected';
                }
            } else {
                $selected = '';
            }
            $value = p2h($value);
            $label = p2h($label);
            $html .= "<option value=\"{$value}\"{$selected}>{$label}</option>";
        }
        $html .= '</select>';

        return $html;
    }

    // }}}
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
