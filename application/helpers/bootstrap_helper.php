<?php defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('formInvalidFeedback'))
{
    function formInvalidFeedback($field)
    {
        $formError = form_error($field);
        if (!empty($formError)) {
            return '<div class="invalid-feedback">' . form_error($field, ' ', ' ') . '</div>';
        }
        return '';
    }
}

if ( ! function_exists('formInvalid'))
{
    function formInvalid($field)
    {
        $formError = form_error($field);
        if (!empty($formError)) {
            return ' is-invalid';
        }
        return '';
    }
}