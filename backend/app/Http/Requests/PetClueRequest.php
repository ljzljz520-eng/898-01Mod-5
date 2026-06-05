<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PetClueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
            'seen_at' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            'is_private' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'lat.required' => '请在地图上标注线索位置',
            'lat.numeric' => '纬度格式不正确',
            'lat.between' => '纬度范围无效',
            'lng.required' => '请在地图上标注线索位置',
            'lng.numeric' => '经度格式不正确',
            'lng.between' => '经度范围无效',
            'seen_at.required' => '请选择看到宠物的时间',
            'seen_at.date' => '时间格式不正确',
            'photo.image' => '上传的文件必须是图片',
            'photo.mimes' => '图片格式只能是 jpeg、png、jpg 或 gif',
            'photo.max' => '图片大小不能超过 5MB',
        ];
    }
}
