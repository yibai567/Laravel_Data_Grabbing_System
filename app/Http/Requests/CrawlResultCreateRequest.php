<?php

namespace App\Http\Requests;

use App\Models\CrawlResult;
use Illuminate\Foundation\Http\FormRequest;

class CrawlResultCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'crawl_task_id' => 'string|nullable',
            'original_data' => 'string|nullable',
            'task_start_time' => 'string|nullable',
            'task_end_time' => 'string|nullable',
            'setting_selectors' => 'string|nullable',
            'setting_keywords' => 'string|nullable',
            'setting_data_type' => 'string|nullable',
            'task_url' => 'string|nullable',
            'format_data' => 'string|nullable',
            'status' => 'string|nullable',
        ];
    }

    public function postFillData()
    {
        return [
            'crawl_task_id' => $this->crawl_task_id,
            'original_data' => $this->original_data,
            'task_start_time' => $this->task_start_time,
            'task_end_time' => $this->task_end_time,
            'task_url' => $this->task_url,
            'format_data' => $this->format_data,
            'setting_selectors' => $this->setting_selectors,
            'setting_keywords' => $this->setting_keywords,
            'setting_data_type' => $this->setting_data_type,
            'status' => CrawlResult::IS_UNTREATED,
        ];
    }
}
