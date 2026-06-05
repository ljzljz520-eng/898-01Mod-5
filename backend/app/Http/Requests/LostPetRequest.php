<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LostPetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_name' => ['nullable', 'string', 'max:100'],
            'pet_type' => ['required', 'string', 'in:dog,cat,other'],
            'breed' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'collar_features' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            'last_seen_lat' => ['required', 'numeric', 'between:-90,90'],
            'last_seen_lng' => ['required', 'numeric', 'between:-180,180'],
            'last_seen_address' => ['required', 'string', 'max:255'],
            'last_seen_at' => ['required', 'date'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'contact_name' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'pet_type.required' => '请选择宠物类型',
            'pet_type.in' => '宠物类型无效',
            'last_seen_lat.required' => '请在地图上标注最后出现地点',
            'last_seen_lat.numeric' => '纬度格式不正确',
            'last_seen_lat.between' => '纬度范围无效',
            'last_seen_lng.required' => '请在地图上标注最后出现地点',
            'last_seen_lng.numeric' => '经度格式不正确',
            'last_seen_lng.between' => '经度范围无效',
            'last_seen_address.required' => '请填写最后出现地址',
            'last_seen_at.required' => '请选择最后出现时间',
            'last_seen_at.date' => '时间格式不正确',
            'contact_phone.required' => '请填写联系电话',
            'photo.image' => '上传的文件必须是图片',
            'photo.mimes' => '图片格式只能是 jpeg、png、jpg 或 gif',
            'photo.max' => '图片大小不能超过 5MB',
        ];
    }
}
