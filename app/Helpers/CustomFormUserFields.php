<?php

namespace App\Helpers;

use App\Models\CustomUserField;
use App\Models\CustomUserFieldValue;
use App\Models\Role;
use Illuminate\Validation\Validator;

class CustomFormUserFields
{
    public static function getCustomUserFields($role_slug, $custom_user_field_values = [])
    {
        $role = Role::where('slug', $role_slug)->first();
        if (is_null($role)) {
            return '';
        }
        $custom_user_fields = CustomUserField::where('company_id', session('current_company'))->whereRoleId($role->id)->get();
        $return = '';
        foreach ($custom_user_fields as $custom_field) {
            $c_values = (array_key_exists($custom_field->name, $custom_user_field_values)) ? $custom_user_field_values[$custom_field->name] : '';
            $options = explode(',', $custom_field->options);
            $required = ($custom_field->is_required) ? 'required' : '';
            $return .= '<div class="form-group">';
            $return .= '<label for="'.$custom_field->name.'" class="control-label '.$required.'">'.$custom_field->title.'</label>';
            if ($custom_field->type == 'select') {
                $return .= '<select class="form-control input-xlarge" placeholder="'.trans('custom_user_field.select_one').'" id="'.$custom_field->name.'" name="'.$custom_field->name.'"'.$required.'>
        <option value="">'.trans('custom_user_field.select_one').'</option>';
                foreach ($options as $option) {
                    if ($option == $c_values) {
                        $return .= '<option value="'.$option.'" selected>'.ucfirst($option).'</option>';
                    } else {
                        $return .= '<option value="'.$option.'">'.ucfirst($option).'</option>';
                    }
                }
                $return .= '</select>';
            } elseif ($custom_field->type == 'radio') {
                $return .= '<div>
            <div class="radio">';
                foreach ($options as $option) {
                    if ($option == $c_values) {
                        $checked = 'checked';
                    } else {
                        $checked = '';
                    }
                    $return .= '<label><input type="radio" name="'.$custom_field->name.'" id="'.$custom_field->name.'" value="'.$option.'" '.$required.' '.$checked.' class="icheck"> '.ucfirst($option).'</label> ';
                }
                $return .= '</div>
        </div>';
            } elseif ($custom_field->type == 'checkbox') {
                $return .= '<div>
            <div class="checkbox">';
                foreach ($options as $option) {
                    if (in_array($option, explode(',', $c_values))) {
                        $checked = 'checked';
                    } else {
                        $checked = '';
                    }
                    $return .= '<label><input type="checkbox" name="'.$custom_field->name.'[]" id="'.$custom_field->name.'" value="'.$option.'" '.$checked.' '.$required.' class="icheck"> '.ucfirst($option).'</label> ';
                }
                $return .= '</div>
        </div>';
            } elseif ($custom_field->type == 'textarea') {
                $return .= '<textarea class="form-control" data-limit="'.config('config.textarea_limit').'" placeholder="'.$custom_field->title.'" name="'.$custom_field->name.'" cols="30" rows="3" id="'.$custom_field->name.'"'.$required.' data-show-counter=1 data-autoresize=1>'.$c_values.'</textarea><span class="countdown"></span>';
            } else {
                $return .= '<input class="form-control '.(($custom_field->type == 'date') ? 'datepicker' : '').'" value="'.$c_values.'" placeholder="'.$custom_field->title.'" name="'.$custom_field->name.'" type="text" value="" id="'.$custom_field->name.'"'.$required.' '.(($custom_field->type == 'date') ? 'readonly' : '').'>';
            }
            $return .= '</div>';
        }

        return $return;
    }

    public static function putCustomHeads($role_slug, $col_heads)
    {
        $role = Role::where('slug', $role_slug)->first();
        if (is_null($role)) {
            return [];
        }
        $custom_user_fields = CustomUserField::where('company_id', session('current_company'))->whereRoleId($role->id)->get();
        foreach ($custom_user_fields as $custom_field) {
            array_push($col_heads, $custom_field->title);
        }

        return $col_heads;
    }

    public static function validateCustomUserField($role_slug, $request)
    {
        $role = Role::where('slug', $role_slug)->first();
        if (is_null($role)) {
            return [];
        }
        $custom_validation = [];
        $custom_user_fields = CustomUserField::where('company_id', session('current_company'))->whereRoleId($role->id)->get();
        $friendly_names = [];
        foreach ($custom_user_fields as $custom_field) {
            if ($custom_field->is_required) {
                $custom_validation[$custom_field->name] = 'required'.(($custom_field->type == 'date') ? '|date' : '').(($custom_field->type == 'number') ? '|numeric' : '').(($custom_field->type == 'email') ? '|email' : '').(($custom_field->type == 'url') ? '|url' : '');
                $friendly_names[$custom_field->name] = $custom_field->title;
            }
        }
        $validation = Validator::make($request->all(), $custom_validation);
        $validation->setAttributeNames($friendly_names);

        return $validation;
    }

    public static function fetchCustomValues($role_slug, $user_id)
    {
        $role = Role::where('slug', $role_slug)->first();
        if (is_null($role)) {
            return '';
        }
        $custom_user_fields = CustomUserField::where('company_id', session('current_company'))->where('role_id', $role->id)->get();
        $return = '';
        foreach ($custom_user_fields as $custom_field) {
            $cf_values = CustomUserFieldValue::where('user_id', $user_id)->where('custom_field_id', $custom_field->id)->first();
            $c_values = (isset($cf_values->value)) ? trim($cf_values->value) : '';
            $options = explode(',', $custom_field->options);
            $required = ($custom_field->is_required) ? 'required' : '';
            $return .= '<div class="form-group">';
            $return .= '<label for="'.$custom_field->id.'" class="control-label '.$required.'">'.$custom_field->title.'</label>';
            if ($custom_field->type == 'select') {
                $return .= '<select class="form-control input-xlarge" placeholder="'.trans('custom_user_field.select_one').'" id="'.$custom_field->id.'" name="'.$custom_field->id.'"'.$required.'>
        <option value="">'.trans('custom_user_field.select_one').'</option>';
                foreach ($options as $option) {
                    $option = trim($option);
                    if ($option == $c_values) {
                        $return .= '<option value="'.$option.'" selected>'.ucfirst($option).'</option>';
                    } else {
                        $return .= '<option value="'.$option.'">'.ucfirst($option).'</option>';
                    }
                }
                $return .= '</select>';
            } elseif ($custom_field->type == 'radio') {
                $return .= '<div>
            <div class="radio">';
                foreach ($options as $option) {
                    $option = trim($option);
                    if ($option == $c_values) {
                        $checked = 'checked';
                    } else {
                        $checked = '';
                    }
                    $return .= '<label><input type="radio" name="'.$custom_field->id.'" id="'.$custom_field->id.'" value="'.$option.'" '.$required.' '.$checked.' class="icheck"> '.ucfirst($option).'</label> ';
                }
                $return .= '</div>
        </div>';
            } elseif ($custom_field->type == 'checkbox') {
                $return .= '<div>
            <div class="checkbox">';
                foreach ($options as $option) {
                    $option = trim($option);
                    if (in_array($option, explode(',', $c_values))) {
                        $checked = 'checked';
                    } else {
                        $checked = '';
                    }
                    $return .= '<label><input type="checkbox" name="'.$custom_field->id.'[]" id="'.$custom_field->id.'" value="'.$option.'" '.$checked.' '.$required.' class="icheck"> '.ucfirst($option).'</label> ';
                }
                $return .= '</div>
        </div>';
            } elseif ($custom_field->type == 'textarea') {
                $return .= '<textarea class="form-control" data-limit="'.config('config.textarea_limit').'" placeholder="'.$custom_field->title.'" name="'.$custom_field->id.'" cols="30" rows="3" id="'.$custom_field->id.'"'.$required.' data-show-counter=1 data-autoresize=1>'.$c_values.'</textarea><span class="countdown"></span>';
            } else {
                $return .= '<input class="form-control '.(($custom_field->type == 'date') ? 'datepicker' : '').'" value="'.$c_values.'" placeholder="'.$custom_field->title.'" name="'.$custom_field->id.'" type="text" value="" id="'.$custom_field->id.'"'.$required.' '.(($custom_field->type == 'date') ? 'readonly' : '').'>';
            }
            $return .= '</div>';
        }

        return $return;
    }

    public static function getCustomUserFieldValues($role_slug, $user_id)
    {
        $role = Role::where('slug', $role_slug)->first();
        if (is_null($role)) {
            return [];
        }

        return CustomUserField::join('custom_user_field_values', 'custom_user_field_values.custom_field_id', '=', 'custom_user_fields.id')
            ->whereRoleId($role->id)
            ->where('custom_user_fields.company_id', session('current_company'))
            ->where('custom_user_field_values.user_id', '=', $user_id)
            ->select('value', 'name')->get();
    }

    public static function storeCustomUserField($role_slug, $user_id, $request)
    {
        $role = Role::where('slug', $role_slug)->first();
        if (is_null($role)) {
            return null;
        }
        $custom_user_fields = CustomUserField::where('company_id', session('current_company'))->whereRoleId($role->id)->get();
        foreach ($custom_user_fields as $custom_field) {
            $custom_field_value = new CustomUserFieldValue();
            $value = $request[$custom_field->name];
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $custom_field_value->value = $value;
            $custom_field_value->custom_field_id = $custom_field->id;
            $custom_field_value->user_id = $user_id;
            $custom_field_value->save();
        }
    }

    public static function updateCustomUserField($role_slug, $user_id, $request)
    {
        $role = Role::where('slug', $role_slug)->first();
        if (is_null($role)) {
            return null;
        }
        $custom_user_fields = CustomUserField::where('company_id', session('current_company'))->whereRoleId($role->id)->get();
        foreach ($custom_user_fields as $custom_field) {
            $value = isset($request[$custom_field->id]) ? $request[$custom_field->id] : '';
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $custom = CustomUserFieldValue::where('custom_field_id', $custom_field->id)
                ->where('user_id', '=', $user_id)
                ->first();

            if ($custom) {
                $custom->value = $value;
                $custom->save();
            } else {
                $custom_field_value = new CustomUserFieldValue;
                $custom_field_value->value = $value;
                $custom_field_value->custom_field_id = $custom_field->id;
                $custom_field_value->user_id = $user_id;
                $custom_field_value->save();
            }
        }
    }

    public static function getCustomUserFieldValueList($user_id)
    {
        $custom_fields = CustomUserFieldValue::where('user_id', '=', $user_id)->select('value')->get()->toArray();
        $fields_lists = '';
        $separator = '';
        foreach ($custom_fields as $item) {
            $fields_lists .= $separator.$item['value'];
            $separator = ',';
        }

        return $fields_lists;
    }

    public static function toWord($word)
    {
        $word = str_replace('_', ' ', $word);
        $word = str_replace('-', ' ', $word);
        $word = ucwords($word);

        return $word;
    }
}
