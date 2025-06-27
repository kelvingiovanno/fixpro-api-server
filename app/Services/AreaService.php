<?php

namespace App\Services;

use App\Enums\JoinPolicyEnum;
use App\Enums\StorageTypeEnum;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class AreaService {

    public function get_name() : string
    {
        $area_name = Cache::get('area_name');
        
        if (!$area_name) {
            $area_name = SystemSetting::get('area_name') ?? 'Default Area';
            Cache::forever('area_name', $area_name);
        }

        return $area_name;
    }

    public function get_join_policy() : string
    {
        $join_policy = Cache::get('area_join_policy');
        
        if (!$join_policy) {
            $join_policy = SystemSetting::get('area_join_policy') ?? JoinPolicyEnum::APPROVAL_NEEDED->value;
            Cache::forever('area_join_policy', $join_policy);
        }

        return $join_policy;
    }

    public function get_join_form(): array
    {
        $form_data = Cache::get('area_join_form');

        if (!$form_data) {
            $form_data = SystemSetting::get('area_join_form');
            Cache::forever('area_join_form', $form_data);
        }

        return json_decode($form_data, true) ?? [];
    }

    public function get_sla_response() : int
    {
        $sla_response = Cache::get('area_sla_response');

        if (!$sla_response) {
            $sla_response = SystemSetting::get('area_sla_response') ?? 86400;
            Cache::forever('area_sla_response', $sla_response);
        }

        return (int) $sla_response;
    }

    public function get_sla_close() : int
    {
        $sla_close = Cache::get('area_sla_close');

        if (!$sla_close) {
            $sla_close = SystemSetting::get('area_sla_close') ?? 172800;
            Cache::forever('area_sla_close', $sla_close);
        }

        return (int) $sla_close;
    }

    public function get_storage_type()
    {
        $type = Cache::get('area_storage_type');

        if (!$type) {
            $type = SystemSetting::get('area_storage_type') ?? StorageTypeEnum::LOCAL->value;
            Cache::forever('area_storage_type', $type);
        }

        return $type;
    }

    public function set_name(string $area_name) : string
    {
        SystemSetting::put('area_name', $area_name);
        Cache::forever('area_name', $area_name);

        return $area_name;
    }

    public function set_join_policy(JoinPolicyEnum $policy)
    {
        SystemSetting::put('area_join_policy', $policy->value);
        Cache::forever('area_join_policy', $policy->value);

        return $policy->value;
    }

    public function set_join_form(array $form_data): array
    {
        $formatted_form = array_map(function ($item) {
            $item = trim($item);
            $item = strtolower($item);
            return preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '_', $item));
        }, $form_data);

        $formatted_form[] = 'name';

        $formatted_form = array_values(array_unique($formatted_form));

        SystemSetting::put('area_join_form', json_encode($formatted_form));
        Cache::forever('area_join_form', json_encode($formatted_form));

        return $formatted_form;
    }

    public function set_sla_response(string $sla_response): int
    {
        $hour = (int) $sla_response;
        $sla_response_in_seconds = $hour * 3600;

        SystemSetting::put('area_sla_response', $sla_response_in_seconds);
        Cache::forever('area_sla_response', $sla_response_in_seconds);

        return $hour;
    }

    public function set_sla_close(int $sla_close) : int
    {
        $hour = (int) $sla_close;
        $sla_close_in_seconds = $hour * 3600;

        SystemSetting::put('area_sla_close', $sla_close_in_seconds);
        Cache::forever('area_sla_close', $sla_close_in_seconds);

        return $hour;
    }

    public function set_storage_type(StorageTypeEnum $type) : string
    {
        SystemSetting::put('area_storage_type', $type->value);
        Cache::forever('area_storage_type', $type->value);

        return $type->value;
    }

    public function is_first_joined() : ?string
    {
        $first_join_member = Cache::get('first_member_login');
        
        if(!$first_join_member)
        {
            $first_join_member = SystemSetting::get('first_member_login');
            Cache::forever('first_member_login', $first_join_member);
        }

        return $first_join_member;
    }

    public function mark_first_joined() : void
    {
        SystemSetting::put('first_member_login', '1');
        Cache::forever('first_member_login', '1');
    }
} 