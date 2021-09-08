<?php


namespace App\Service;

use App\Entity\Activity;
use App\Model\ActivityForm;
use App\Repository\ActivityRepository;
use App\Utils\ConfigUtils;
use DateTime;

class ActivityService {

    /**
    * @param ActivityForm $form
    * @return activity[]
    */
    public function createActivity(): ?array { 

        //$user = UserService::getCurrentUser();
        //if(is_null($user))return null;

        $result = [];

        $activity = new Activity();
        $activity->setActivityId('34343');
        $activity->setUserId('');
        $activity->setTimestampCreation('');
        $activity->setType('');
        $activity->setSurveyId('');
        $activity->setTidId('');
        $activity->setTrainingId('');

        $result_insert = ActivityRepository::insertActivity($activity);

        return $result;
    }
}