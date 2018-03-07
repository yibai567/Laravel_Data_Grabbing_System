<?php

namespace App\Http\Requests;

use App\Models\CrawlTask;
use Illuminate\Foundation\Http\FormRequest;

class CrawlTaskCreateRequest extends FormRequest
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
            'name' => 'string|nullable',
            'description' => 'string|nullable',
            'resource_url' => 'string|nullable',
            'cron_type' => 'integer|nullable',
            'selectors' => 'string|nullable',
        ];
    }

    public function postFillData()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'resource_url' => $this->resource_url,
            'cron_type' => $this->cron_type,
            'selectors' => $this->selectors,
            'status' => CrawlTask::IS_INIT,
            'response_type' => CrawlTask::RESPONSE_TYPE_API,
            'response_url' => '',
            'response_params' => '',
            'test_time' => null,
            'test_result' => '',
        ];
    }
}
