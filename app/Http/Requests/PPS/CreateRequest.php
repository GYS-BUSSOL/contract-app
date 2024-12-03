<?php

namespace App\Http\Requests\PPS;

use App\Models\MerBUCCWC;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file_attachment' => 'nullable|file|extensions:doc,pdf,gif,jpeg,png|max:2048',
            'company' => 'required|string|exists:mer_company,company_code',
            'bu' => 'required|string|exists:mer_bu_cc_wc,number',
            'cc.*' => [
                'required',
                Rule::forEach(function (string|null $value, string $attribute) {
                    return [
                        Rule::exists(MerBUCCWC::class, 'number')
                    ];
                })
            ],
            'wc.*' => [
                'nullable',
                Rule::forEach(function (string|null $value, string $attribute) {
                    return [
                        Rule::exists(MerBUCCWC::class, 'number')
                    ];
                })
            ],
            'work_location' => 'nullable|array',
            'id_project' => 'nullable|string',
            'pps_no' => 'required|string|unique:trn_contract,con_pps_no',
            'old_pps_no' => 'nullable|string',
            'priority' => 'required|in:segera,tidak segera',
            'shift_checklist.*' => ['required', Rule::in(['Non Shift', 'Shift 1', 'Shift 2', 'Shift 3'])],
            'cp_name' => 'required|numeric',
            'cp_dept' => 'required|string',
            'cp_ext' => 'required|numeric',
            'cp_email' => 'required|email',
            'comment' => 'nullable|string',
            'duration' => 'nullable|string',
            'suggest_vendor' => 'nullable|string|exists:mer_vendor,vnd_id',
            'con_comment_jobtarget' => 'nullable|string'
        ];
    }
}
