<?php


namespace App\Service;

use App\Entity\Answer;
use App\Entity\Survey;
use App\Entity\User;
use App\Model\AnswerForm;
use App\Model\AnswerType;
use App\Model\ListAnswerForm;
use App\Model\ListSurveyForm;
use App\Model\Locale;
use App\Model\SurveyForm;
use App\Model\SurveyState;
use App\Model\SurveyType;
use App\Model\UpdateSurveyForm;
use App\Repository\AnswerRepository;
use App\Repository\SnoozeRepository;
use App\Repository\SurveyRepository;
use App\Repository\UserSettingsRepository;
use App\Utils\ConfigUtils;
use App\Utils\Utils;
use DateTime;
use Google\Service\Exception;

class SurveyService{

    public static function registerSurvey(SurveyForm $form) : ?Survey{
		$user = UserService::getCurrentUser();
		if(is_null($user))return null;
        $survey = new Survey();
        $survey->setBegin($form->getBegin());
        $survey->setStarted($form->getStart());
        $survey->setEnd($form->getEnd());
        $survey->setCode($form->getCode());
        $survey->setState($form->getState());
		$survey->setUser($user);
		return SurveyRepository::save($survey);
	}
    public static function list() : ?array{
        $user = UserService::getCurrentUser();
        if(is_null($user)){
            return null;
        }
        return SurveyRepository::findByUser($user);
    }
    public static function listForm(ListSurveyForm $form) : ?array{
        $user = UserService::getCurrentUser();
        if(is_null($user)){
            return null;
        }
        return SurveyRepository::findByUserAndStateAndCodeAndStartAtAfterAndEndAtBefore(
            $user,
            $form->getState(),
            $form->getType(),
            $form->getStartDate(),
            $form->getEndDate()
        );
    }
    public static function listType(SurveyType $type) : ?array{
        $user = UserService::getCurrentUser();
        if(is_null($user))return null;
        return SurveyRepository::findByUserAndType($user, $type);
    }
    private static function createRandom($user, DateTime $begin, DateTime $end, int $duration): Survey{
        if($user instanceof User){
            $user = $user->getUserId();
        }
        $survey = new Survey();
        $survey->setCode(SurveyType::$RANDOM->getCode());
        $survey->setUserId($user);
        $endts = $end->getTimestamp()-$duration*60;
        if($endts <= $begin->getTimestamp()){
            $begints = $begin->getTimestamp();
        }else{
            $begints = rand($begin->getTimestamp(), $endts);
        }
        $beginDate = new DateTime();
        $beginDate->setTimestamp($begints);
        $survey->setBegin($beginDate);
        $endDate = new DateTime();
        $endDate->setTimestamp($begints+($duration*60));
        if($endDate > $end){
            $endDate = $end;
        }
        $survey->setEnd($endDate);
        $survey->setState(SurveyState::$PENDING);
        return $survey;
    }
    public static function createRandoms(){
        Utils::log("Creating randoms.");
        $duration = ConfigUtils::getInt("RANDOM_DURATION");
        $cooldown = ConfigUtils::getInt("RANDOM_COOLDOWN");
        $page = 1;
        $settings = UserSettingsRepository::getPage($page);
        $now = new DateTime();
        while(!is_null($settings) && !empty($settings)){
            Utils::log("Creating random for page " . $page);
            foreach ($settings as $setting){
                $wakeup = $setting->getWakeup();
                $sleep = $setting->getSleep();
                $start = clone $now;
                $end = clone $now;
                $split = explode(":", $wakeup);
                $start->setTime((int)$split[0], (int)$split[1]);
                $split = explode(":", $sleep);
                $end->setTime((int)$split[0], (int)$split[1]);
                if($end < $start){
                    $end->modify("+ 1 day");
                }
                if($now < $start || $end < $now)continue;
                $step = floor(( $end->getTimestamp() - $start->getTimestamp() ) / 8);
                $elapsed = $now->getTimestamp() - $start->getTimestamp();
                $group = floor($elapsed/$step);
                $start_timestamp = $start->getTimestamp() + $step * $group;
                $end_timestamp = $start_timestamp + $step;
                $start_group = clone $now;
                $start_group->setTimestamp($start_timestamp+60);
                $end_group = clone $now;
                $end_group->setTimestamp($end_timestamp);
                if( ($now->getTimestamp() + $duration*60) > $end_timestamp )continue;
                $result = SurveyRepository::findByUserAndStateAndCodeAndStartAtAfterAndEndAtBefore($setting->getUserId(), null , SurveyType::$RANDOM->getCode(), $start_group, $end_group);
                if(!isset($result) || is_null($result) || sizeof($result) === 3)continue;
                $completed = false;
                $last = clone $start_group;
                foreach ($result as $row){
                    if($row->getState() === SurveyState::$CLOSED->getCode()){
                        $completed=true;
                        break;
                    }elseif($row->getEnd() > $last){
                        $last = $row->getEnd();
                    }
                }
                if($completed || $last >= $now){
                    continue;
                }

                ($start_random = new DateTime())->setTimestamp($now->getTimestamp() + 60);
                ($end_random = new DateTime())->setTimestamp($end_timestamp);
                if(sizeof($result) === 0){
                    $temp_end = clone $now;
                    $temp_start = new DateTime();
                    $temp_start->setTimestamp($temp_end->getTimestamp() - 60 * $cooldown);
                    $result = SurveyRepository::findByUserAndStateAndCodeAndStartAtAfterAndEndAtBefore($setting->getUserId(), SurveyState::$CLOSED , SurveyType::$RANDOM->getCode(), $temp_start, $temp_end, SurveyRepository::ENDED_IN_RANGE);
                    if(isset($result) && !is_null($result) && sizeof($result) !== 0) {
                        $temp_date = null;
                        foreach ($result as $row){
                            if(is_null($temp_date) || $temp_date < $row->getEnded()){
                                $temp_date = $row->getEnded();
                            }
                        }
                        if(!is_null($temp_date)){
                            $start_random->setTimestamp($temp_date->getTimestamp()+(60*$cooldown));
                        }
                    }
                }else if($start_random->getTimestamp() < $last->getTimestamp() + 60 * $cooldown){
                    $start_random->setTimestamp($last->getTimestamp() + 60 * $cooldown);
                }
                $survey = self::createRandom($setting->getUserId(), $start_random, $end_random, $duration);
                SurveyRepository::save($survey);
            }
            $page = $page +1;
            $settings = UserSettingsRepository::getPage($page);
        }
    }
    public static function isFirstOfTheDay(int $id, $state) : ?bool{
        $user = UserService::getCurrentUser();
        if(is_null($user)){
            Utils::log("Not logged");
            return null;
        }
        $survey = SurveyRepository::getOne($id);
        if(is_null($survey)){
            Utils::log("Invalid survey id : " . $id);
            return null;
        }
        if(is_null($user) || is_null($survey->getUser()) || $survey->getUser()->getUserId() != $user->getUserId()){
            Utils::log("Invalid survey owner");
            return null;
        }
        if(!is_null($state)){
            $state = SurveyState::get($state);
        }
        if(!is_null($state)){
            $state = $state->getCode();
        }
        return SurveyRepository::isFirstOfTheDay($user, $survey, $state);
    }
    public static function updateSurvey(UpdateSurveyForm $form): bool{
        $user = UserService::getCurrentUser();
        if(is_null($user)){
            Utils::log("Not logged");
            return false;
        }
        $survey = SurveyRepository::getOne($form->getId());
        if(is_null($survey)){
            Utils::log("Invalid survey id : " . $form->getId());
            return false;
        }
        if(is_null($user) || is_null($survey->getUser()) || $survey->getUser()->getUserId() != $user->getUserId()){
            Utils::log("Invalid survey owner");
            return false;
        }
        if(!is_null($form->getStart())){
            $survey->setStarted($form->getStart());
        }
        if(!is_null($form->getEnd())){
            $survey->setEnded($form->getEnd());
        }
        if(!is_null($form->getState())){
            $survey->setState($form->getState());
        }
        SurveyRepository::save($survey);
        return true;
    }
    public static function registerAns(AnswerForm $form){
        $survey = SurveyRepository::getOne($form->getSurveyId());
		if(is_null($survey)){
            Utils::log("Invalid survey id");
		    return "Invalid survey id";
        }
		$user = UserService::getCurrentUser();
		if(is_null($user) || is_null($survey->getUser()) || $survey->getUser()->getUserId() != $user->getUserId()){
            Utils::log("Invalid survey owner");
		    return "Invalid survey owner";
        }
		$result = [];
		foreach ($form->getForm() as $current){
            $type = AnswerType::getFromCode($current->getCode());
            if(is_null($type))return null;
            $ans = new Answer();
            $ans->setSurvey($survey);
            $ans->setCode($type->getCode());
            $ans->setList($current->getList());

            $ans->setQuantity($current->getQuantity());
            $ans->setSurvey($survey);
            array_push($result, AnswerRepository::save($ans));
        }

		return $result;
    }
    public static function listAns(ListAnswerForm $form): ?array{
        $user = UserService::getCurrentUser();
        if(is_null($user) )return null;
        if(!is_null($form->getType())){
            $type = AnswerType::getFromCode($form->getType());
            if(is_null($type)){
                //TODO invalid request
                return null;
            }
        }
        if(is_null($form->getSurveyId())){
            return AnswerRepository::findBySurveyInAndCodeAndCreatedAtAfterAndCreatedAtBefore(
                self::list(),
                $form->getType(),
                $form->getStartDate(),
                $form->getEndDate()
            );
        }else{
            $survey = SurveyRepository::getOne($form->getSurveyId());
			if(is_null($survey))return null;
			if($survey->getUser()->getUserId() != $user->getUserId())return null;
            return AnswerRepository::findBySurveyAndCodeAndCreatedAtAfterAndCreatedAtBefore(
                $survey,
                $form->getType(),
                $form->getStartDate(),
                $form->getEndDate()
            );
        }
    }

    public static function notifyRandom(){
        $start = new DateTime();
        $start->modify("- 10 minutes");
        $end = new DateTime();
        $end->modify("+ 5 minutes");
        Utils::log(json_encode($start->format(DATE_ISO8601)));
        Utils::log(json_encode($end->format(DATE_ISO8601)));
        $result = SurveyRepository::findByUserAndStateAndCodeAndStartAtAfterAndEndAtBefore(null, /*SurveyState::$PENDING->getCode()*/null, SurveyType::$RANDOM->getCode(), $start, $end);
        $message = [];
        foreach($result as $row){
            if($start <= $row->getBegin() && $end > $row->getBegin() && ($row->getState() !== SurveyState::$CLOSED->getCode())){
                $settings = UserSettingsRepository::getByUserId($row->getUserId());
                if(is_null($settings->getDeviceToken()))continue;
                $snooze = SnoozeRepository::getCurrentSnooze($row->getUserId());
                if(!empty($snooze))continue;

                $locale = Locale::getFromCode($settings->getLocale());
                $token = $settings->getDeviceToken();
                if(!isset($message[$locale->getCode()])){
                    $message[$locale->getCode()] = [];
                }
                $id = floor(($row->getBegin()->getTimestamp()-$end->getTimestamp())/-300);
                if($id == 1)continue;
                if($id > 0){
                    $id = $id-1;
                }
                if(!isset($message[$locale->getCode()][$id])){
                    $message[$locale->getCode()][$id] = NotificationService::getRandomMessage($locale, $id);
                }
                $current = $message[$locale->getCode()][$id];
                try {
                    NotificationService::sendNotification($current, $token, $row->getSurveyId());
                }catch (Exception $exception){
                    Utils::log($exception->getMessage());
                }
            }
        }
    }
}