<?php

namespace App\Helpers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Validator;

class ExcelfileValidator
{
    public static function validate($request)
    {
        $validator = Validator::make(
            [
                'file' => $request->file('file'),
                'extension' => strtolower($request->file('file')->getClientOriginalExtension()),
            ],
            [
                'file' => 'required',
                'extension' => 'required|in:csv',
            ]
        );
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        return true;
    }
}
