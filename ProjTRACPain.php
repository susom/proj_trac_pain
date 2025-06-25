<?php
namespace Stanford\ProjTRACPain;

require_once "emLoggerTrait.php";

//use ExternalModules\ExternalModules;
use ExternalModules\AbstractExternalModule;
use REDCap;

class ProjTRACPain extends AbstractExternalModule {
    use emLoggerTrait;

    
    // Add your module methods and hooks here

    /**
     * Example method to report survey completion
     * @return array
     */
    public function reportSurveyCompletion() {
        $project_id = defined('PROJECT_ID') ? PROJECT_ID : null;
        $this->emDebug('project_id', $project_id);
        if (!$project_id) return [];

        // Step 1: Get baseline_arm_2 data
        $params_recruit = [
            'project_id' => $project_id,
            'fields' => ['record_id', 'myphd_id', 'youth_intro_day'],
            'events' => ['baseline_arm_2'],
            'return_format' => 'array'
        ];
        $recruit_data = \REDCap::getData($params_recruit);
        $this->emDebug('baseline_arm_2 data', $recruit_data);

        // Step 2: Get daily_arm_4 data
        $params_daily = [
            'project_id' => $project_id,
            'fields' => ['myphd_id', 'checkin_complete_complete'],
            'events' => ['daily_arm_4'],
            'return_format' => 'array'
        ];
        $daily_data = \REDCap::getData($params_daily);
        $this->emDebug('daily_arm_4 data', $daily_data);

        // Step 3: Build a lookup for daily_arm_4 by myphd_id
        $daily_lookup = [];
        foreach ($daily_data as $record) {
            foreach ($record as $event) {
                if (isset($event['myphd_id'])) {
                    $daily_lookup[$event['myphd_id']][] = $event['checkin_complete_complete'] ?? null;
                }
            }
        }

        // Step 4: Build results with count
        $results = [];
        foreach ($recruit_data as $record) {
            foreach ($record as $event) {
                if (is_array($event)) {
                    $myphd_id = $event['myphd_id'] ?? '';
                    $count = 0;
                    if ($myphd_id && isset($daily_lookup[$myphd_id])) {
                        // Count the number of times checkin_complete_complete == 2
                        $count = count(array_filter($daily_lookup[$myphd_id], function($v) { return $v == 2; }));
                    }
                    // Calculate number of days since enrollment
                    $youth_intro_day = $event['youth_intro_day'] ?? '';
                    $days_since_enrollment = '';
                    if (!empty($youth_intro_day)) {
                        $enroll_date = new \DateTime($youth_intro_day);
                        $today = new \DateTime();
                        $interval = $enroll_date->diff($today);
                        $days_since_enrollment = $interval->days;
                    }
                    $results[] = [
                        'record_id' => $event['record_id'] ?? '',
                        'myphd_id' => $myphd_id,
                        'EnrollmentDate' => $youth_intro_day,
                        'CheckinCount' => $count,
                        'Number of Days Since Enrollment' => $days_since_enrollment,
                        '% of Days Completed Since Enrolled' => ($days_since_enrollment > 0) ? round(($count / $days_since_enrollment) * 100, 1) . '%' : ''
                    ];
                }
            }
        }
        return $results;
    }
} 