<?php

namespace App\Enums;

enum IssueLogTypeEnum : int
{
    case ASSESSMENT = 1;
    case WORK_PROGRESS = 2;
    case WORK_EVALUATION_REQUEST = 3;
    case WORK_EVALUATION = 4;
    case ACTIVITY_LOG = 5;

    public function label() : string
    {
        return match($this) {
            self::ASSESSMENT => 'Assessment',
            self::WORK_PROGRESS => 'Work Progress',
            self::WORK_EVALUATION_REQUEST => 'Work Evaluation Request',
            self::WORK_EVALUATION => 'Work Evaluation',
            self::ACTIVITY_LOG => 'Activity Log',
        };
    }
}