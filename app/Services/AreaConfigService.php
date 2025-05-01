<?php

namespace App\Services;

use App\Models\Area;

class AreaConfigService
{
    private function getArea(): Area
    {
        $area = Area::first();
        return $area ?? Area::create();
    }

    public function isSetUp(): bool {
        return $this->getArea()->is_set_up === 1;
    }

    public function markAsSetUp() : void {
        $this->getArea()->update(['is_set_up' => 1]);
    }

    public function setName(string $name) : void {
        $this->getArea()->update(['name' => $name]);
    }

    public function setJoinPolicy(string $policy): void
    {
        $this->getArea()->update(['join_policy' => $policy]);
    }
   
    public function setJoinForm(array $form): void
    {
        $this->getArea()->update([
            'join_form' => json_encode($form),
        ]);
    }

    public function incrementMemberCount(int $by = 1): void
    {
        $this->getArea()->increment('member_count', $by);
    }

    public function incrementPendingMemberCount(int $by = 1): void
    {
        $this->getArea()->increment('pending_member_count', $by);
    }

    public function decrementMemberCount(int $by = 1): void
    {
        $this->getArea()->decrement('member_count', $by);
    }

    public function decrementPendingMemberCount(int $by = 1): void
    {
        $this->getArea()->decrement('pending_member_count', $by);
    }

    public function getMemberCount(): int
    {
        return $this->getArea()->member_count;
    }

    public function getPendingMemberCount(): int
    {
        return $this->getArea()->pending_member_count;
    }

    public function getJoinPolicy(): string
    {
        return $this->getArea()->join_policy;
    }

    public function getJoinForm(): ?array
    {
        $joinForm = $this->getArea()->join_form;

        if (!$joinForm) {
            return null;
        }

        $decoded = json_decode($joinForm, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function getAreaData()
    {
        $areaData = $this->getArea();
        unset($areaData['updated_at'], $areaData['created_at'], $areaData['join_form'], $areaData['id']) ;

        return $areaData;
    }
}
