<?php

namespace App\Enums;

enum SurveyQuestionType: string
{
    case OpenEnded = 'open_ended';
    case YesNo = 'yes_no';
    case MultipleChoice = 'multiple_choice';
    case RatingScale = 'rating_scale';
    case Ranking = 'ranking';
}
