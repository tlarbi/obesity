<?php

use App\Entity\Survey;
use App\Model\SurveyType;
use App\Repository\SurveyRepository;
use App\Service\UserService;
use App\Utils\Utils;

require_once '../vendor/autoload.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

const SURVEY_RANDOM_PER_DAY = 4;
const SURVEY_AFTER_EATING_PER_DAY = 3;

// Check login
$user = UserService::getCurrentUser();
$returnData = [];
if($_SERVER["REQUEST_METHOD"] != "GET") {
    $returnData = Utils::msg(0, 404, 'Page Not Found!');
}elseif(!is_null($user)){
    // Request daily and weekly reports
    $dailyAchievements = SurveyRepository::getDailyAchievement($user);

    SurveyType::init();

    $dailyRandom = 0;
    $dailyEating = 0;
    foreach ($dailyAchievements as $achievementType) {
        switch ($achievementType['survey_type']) {
            case SurveyType::$RANDOM->getCode():
                $dailyRandom = (int) $achievementType['survey_count'];
                break;
            case SurveyType::$AFTER_EATING->getCode():
                $dailyEating = (int) $achievementType['survey_count'];
        }
    }

    $weeklyAchievements = SurveyRepository::getWeeklyAchievement($user);

    $dailySurveys = [];
    foreach ($weeklyAchievements as $dailyAchievement) {
        $dailySurveys[$dailyAchievement['survey_day']][$dailyAchievement['survey_type']] = $dailyAchievement['survey_count'];
    }

    //var_dump($dailySurveys);

    $achievedDayCount = 0;
    foreach ($dailySurveys as $day => $dailySurvey) {
        if (isset($dailySurvey[SurveyType::$RANDOM->getCode()], $dailySurvey[SurveyType::$AFTER_EATING->getCode()])
            && $dailySurvey[SurveyType::$RANDOM->getCode()] >= SURVEY_RANDOM_PER_DAY
            && $dailySurvey[SurveyType::$AFTER_EATING->getCode()] >= SURVEY_AFTER_EATING_PER_DAY
        ) {
            $achievedDayCount++;
        }
    }

    // Format
    $returnData = [
        "daily_random" => $dailyRandom,
        "daily_eating" => $dailyEating,
        "weekly_achieved" => $achievedDayCount,
    ];
}else{
    $returnData = Utils::msg(0,401,"Unauthorized");
}

echo json_encode($returnData);