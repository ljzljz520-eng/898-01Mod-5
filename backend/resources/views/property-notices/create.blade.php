@extends('layouts.app')

@section('title', '发布物业公告')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">发布物业公告</h1>
    
    <div class="card">
        <form method="POST" action="{{ route('property-notices.store') }}" id="property-notice-form">
            @csrf
            
            <div class="mb-4">
                <label for="notice_type" class="block text-gray-700 text-sm font-medium mb-2">公告类型</label>
                <select id="notice_type" name="notice_type" required
                        class="input-field @error('notice_type') border-red-500 @enderror">
                    <option value="">请选择公告类型</option>
                    @foreach($noticeTypes as $key => $name)
                        <option value="{{ $key }}" {{ old('notice_type') == $key ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('notice_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-medium mb-2">标题</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                       class="input-field @error('title') border-red-500 @enderror"
                       placeholder="请输入公告标题" autofocus>
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="content" class="block text-gray-700 text-sm font-medium mb-2">内容</label>
                <textarea id="content" name="content" rows="12" required
                          class="input-field @error('content') border-red-500 @enderror"
                          placeholder="请输入公告内容...">{{ old('content') }}</textarea>
                @error('content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">接收范围</label>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600 mb-3">选择需要接收该公告的住户范围，不选则默认发送给所有住户</p>
                    
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="select_all_buildings" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700">全选所有楼栋</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="buildings-container">
                        @foreach($buildings as $building)
                            <div class="building-item bg-white border border-gray-200 rounded-lg p-3">
                                <label class="flex items-start gap-2 mb-2">
                                    <input type="checkbox" class="building-checkbox rounded border-gray-300 text-primary-600 focus:ring-primary-500" data-building="{{ $building['name'] }}">
                                    <span class="font-medium text-gray-800">{{ $building['name'] }}</span>
                                </label>
                                @if(!empty($building['units']))
                                    <div class="ml-6 mt-2 space-y-1">
                                        @foreach($building['units'] as $unit)
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" class="unit-checkbox rounded border-gray-300 text-primary-600 focus:ring-primary-500" 
                                                       data-building="{{ $building['name'] }}" 
                                                       data-unit="{{ $unit }}"
                                                       value="{{ $building['name'] }}-{{ $unit }}">
                                                <span class="text-sm text-gray-600">{{ $unit }}单元</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div id="selected-recipients-preview" class="mt-4 hidden">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-sm font-medium text-blue-800 mb-2">已选择范围：</p>
                            <ul class="text-sm text-blue-700" id="selected-list">
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="is_pinned" name="is_pinned" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" {{ old('is_pinned') ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">置顶公告</span>
                </label>
            </div>

            <input type="hidden" id="recipients-input" name="recipients" value="">

            <div class="flex gap-4">
                <button type="submit" class="btn-primary">发布公告</button>
                <a href="{{ route('topics.index') }}" class="btn-secondary">取消</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('property-notice-form');
    const buildingCheckboxes = document.querySelectorAll('.building-checkbox');
    const unitCheckboxes = document.querySelectorAll('.unit-checkbox');
    const selectAllCheckbox = document.getElementById('select_all_buildings');
    const selectedList = document.getElementById('selected-list');
    const selectedPreview = document.getElementById('selected-recipients-preview');
    const recipientsInput = document.getElementById('recipients-input');

    function updateSelectedRecipients() {
        const selected = [];
        
        buildingCheckboxes.forEach(buildingCheckbox => {
            const buildingName = buildingCheckbox.dataset.building;
            const buildingUnits = document.querySelectorAll(`.unit-checkbox[data-building="${buildingName}"]');
            
            if (buildingCheckbox.checked) {
                selected.push({
                    type: 'building',
                    value: buildingName,
                    label: buildingName + ' 全体住户'
                });
            } else {
                buildingUnits.forEach(unitCheckbox => {
                    if (unitCheckbox.checked) {
                        selected.push({
                            type: 'unit',
                            value: unitCheckbox.value,
                            label: buildingName + '号楼 ' + unitCheckbox.dataset.unit + '单元 全体住户'
                        });
                    }
                });
            }
        });

        if (selected.length > 0) {
            selectedPreview.classList.remove('hidden');
            selectedList.innerHTML = selected.map(s => `<li>• ${s.label}</li>`).join('');
            recipientsInput.value = JSON.stringify(selected.map(s => ({ type: s.type, value: s.value })));
        } else {
            selectedPreview.classList.add('hidden');
            recipientsInput.value = '';
        }

        selectAllCheckbox.checked = Array.from(buildingCheckboxes).every(cb => cb.checked);
    }

    buildingCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const buildingName = this.dataset.building;
            const buildingUnits = document.querySelectorAll(`.unit-checkbox[data-building="${buildingName}"]');
            buildingUnits.forEach(unit => {
                unit.checked = this.checked;
            });
            updateSelectedRecipients();
        });
    });

    unitCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const buildingName = this.dataset.building;
            const buildingUnits = document.querySelectorAll(`.unit-checkbox[data-building="${buildingName}"]');
            const buildingCheckbox = document.querySelector(`.building-checkbox[data-building="${buildingName}"]');
            const allUnitsChecked = Array.from(buildingUnits).every(cb => cb.checked);
            buildingCheckbox.checked = allUnitsChecked;
            updateSelectedRecipients();
        });
    });

    selectAllCheckbox.addEventListener('change', function() {
        buildingCheckboxes.forEach(cb => {
            cb.checked = this.checked;
            const buildingName = cb.dataset.building;
            const buildingUnits = document.querySelectorAll(`.unit-checkbox[data-building="${buildingName}"]');
            buildingUnits.forEach(unit => {
                unit.checked = this.checked;
            });
        });
        updateSelectedRecipients();
    });

    form.addEventListener('submit', function(e) {
        if (!document.getElementById('notice_type').value) {
            e.preventDefault();
            alert('请选择公告类型');
            return;
        }
    });

    updateSelectedRecipients();
});
</script>
@endsection
