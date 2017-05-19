<?php
/**
 * Action messages
 * Define those to be used when creating a delta entry
 * 
 * @author itaymoav
 *
 */
final class BL_Action_LogMsgs{
	// for spoiled developer so they do not need to remember it by heart
	// Single use option below - Should be used in one place at appropriate entrance of code (action, start of script)
    	const __ACT_COURSE_ENROLLMENT_REJECT_AD__ 	= '__ACT_COURSE_ENROLLMENT_REJECT_AD__';
    	const __ACT_COURSE_ENROLLMENT_APPROVE_AD__	= '__ACT_COURSE_ENROLLMENT_APPROVE_AD__';
    	const __UPDATE_ORG_ENROLLMENT__				= '__UPDATE_ORG_ENROLLMENT__';
    	const __ADD_ORG_ENROLLMENT__				= '__ADD_ORG_ENROLLMENT__';
    	const __ASSIGN_USER_TO_EDU_GROUP__			= '__ASSIGN_USER_TO_EDU_GROUP__';
    	const __CREATE_EDU_GROUP__					= '__CREATE_EDU_GROUP__';
    	const __EDIT_EDU_GROUP__					= '__EDIT_EDU_GROUP__';
    	const __PAYMENT_RECEIVED__					= '__PAYMENT_RECEIVED__';
    	const __COURSE_ENROLLMENT_SUBMITTED__		= '__COURSE_ENROLLMENT_SUBMITTED__';
    	const __COURSE_ENROLLMENT_CORWIN__			= '__COURSE_ENROLLMENT_CORWIN__';
    	const __USER_RESCHEDULE__					= '__USER_RESCHEDULE__';
    	const __USER_REGISTERED__					= '__USER_REGISTERED__';
    	const __SURVEY_SUBMIT__						= '__SURVEY_SUBMIT__';
    	const __CANCEL_COURSE_ENROLLMENT__			= '__CANCEL_COURSE_ENROLLMENT__';
    	const __USER_MERGED_BY_CSV_AUTOMERGE_PROCESS__	= '__USER_MERGED_BY_CSV_AUTOMERGE_PROCESS__';
    	const __USER_MERGED_BY_CLI__                = '__USER_MERGED_BY_CLI__';
    	const __USER_UNMERGED_BY_CLI__              = '__USER_UNMERGED_BY_CLI__';
    	
    	// General multi-use options below - DO NOT use as only message, only for added depth
    	const __GROUP_ROLLOUT__						= '__GROUP_ROLLOUT__';
    	const __SELF_ENROLL__						= '__SELF_ENROLL__';
    	const __NEW_REGISTRATION__					= '__NEW_REGISTRATION__';
    	const __REGISTRATION_W_EMAIL__				= '__REGISTRATION_W_EMAIL__';
    	const __FIX_DUPLICATE_ENROLLMENTS__			= '__FIX_DUPLICATE_ENROLLMENTS__';
    	const __ROLLOUT_REMOVED__					= '__ROLLOUT_REMOVED__';
    	const __GNOSIS_COMPELTION__					= '__GNOSIS_COMPELTION__';
    	const __GNOSIS_COURSE_COMPLETION__			= '__GNOSIS_COURSE_COMPLETION__';
    	const __FS_TICKET_NUMBER__                  = '__FS_TICKET_NUMBER__';
    	const __CRON_EVENT__                           = '__CRON_EVENT__';
    	const __CLI_RUN__                           = '__CLI_RUN__';
    	const __UPDATE_COURSE_CERTIFICATE_UI__      = '__UPDATE_COURSE_CERTIFICATE_UI__';
    	const __TAKE_OVER_USER__                    = '__TAKE_OVER_USER__';
    	const __USER_TERMINATED_BY_CRON_10__           = '__USER_TERMINATED_BY_CRON_10__';
    	const __USER_TERMINATED_BY_CRON_10_MANUAL__	   = '__USER_TERMINATED_BY_CRON_10_MANUAL__';
    	const __LEARNER_REMOVED_BY_SyncUserRolesOUE__  = '__LEARNER_REMOVED_BY_SyncUserRolesOUE__';
    	const __JOB_REMOVED_BY_SyncUserJobsOUE__       = '__JOB_REMOVED_BY_SyncUserJobsOUE__';
    	const __CLOUD_CME_DEGREE_CE_CREDIT_UPDATE__    = '__CLOUD_CME_DEGREE_CE_CREDIT_UPDATE__';
    	 
	/**
	 * Enforcing enumas - PHP bahhh
	 */
	static private $msgs_codes = [
	// Single use option below
		self::__ACT_COURSE_ENROLLMENT_REJECT_AD__	=> 'Course enrollment rejected by AD',
		self::__ACT_COURSE_ENROLLMENT_APPROVE_AD__	=> 'Course enrollment approved by AD',
		self::__UPDATE_ORG_ENROLLMENT__				=> 'User updated organization enrollment',
		self::__ADD_ORG_ENROLLMENT__				=> 'User added new organization enrollment',
		self::__ASSIGN_USER_TO_EDU_GROUP__			=> 'User was assigned to education group',
		self::__CREATE_EDU_GROUP__					=> 'Education group was created',
		self::__EDIT_EDU_GROUP__					=> 'Education group was editted',
		self::__PAYMENT_RECEIVED__					=> 'Payment was received from paypal(outside)',
		self::__COURSE_ENROLLMENT_SUBMITTED__		=> 'Course enrollment was submitted',
	    self::__COURSE_ENROLLMENT_CORWIN__			=> 'Course Enrollment initiated by Corwin API',
		self::__USER_RESCHEDULE__					=> 'User rescheduled their event enrollment',
		self::__USER_REGISTERED__					=> 'User REGISTERED TO THE SYSTEM',
		self::__SURVEY_SUBMIT__						=> 'User submitted survey',
		self::__CANCEL_COURSE_ENROLLMENT__			=> 'User canceled a course enrollment',
	    self::__USER_TERMINATED_BY_CRON_10__        => 'User was terminated by cron 10 after two years of inactivity',
	    self::__USER_TERMINATED_BY_CRON_10_MANUAL__	=> 'User was terminated by cron 10 manual script run after two years of inactivity',
	    self::__USER_MERGED_BY_CSV_AUTOMERGE_PROCESS__ => 'User was merged by cli/merge_user/app/Automerge/FromCSV...',
	    self::__USER_MERGED_BY_CLI__                => 'Single User Merge CLI',
	    self::__USER_UNMERGED_BY_CLI__              => 'Unmerge User By CLI',
	    self::__LEARNER_REMOVED_BY_SyncUserRolesOUE__  => 'learner removed by SyncUserRolesOUE',
	    self::__JOB_REMOVED_BY_SyncUserJobsOUE__    => 'job removed by SyncUserJobsOUE',
	    
	// General multi-use options below
		self::__GROUP_ROLLOUT__						=> 'Group Rollout',
		self::__SELF_ENROLL__						=> 'Self Enroll',
		self::__NEW_REGISTRATION__					=> 'New registration',
		self::__REGISTRATION_W_EMAIL__				=> 'Registration with existing email',
	    
	// DATAFIX options
		self::__FIX_DUPLICATE_ENROLLMENTS__			=> 'Fixing duplicate enrollment (day,topic,content) from a script.',
		self::__ROLLOUT_REMOVED__					=> 'Rollout removed by enroller',
		self::__GNOSIS_COMPELTION__					=> 'Update transcript and content enrollment with completion within the Gnosis API',
		self::__GNOSIS_COURSE_COMPLETION__			=> 'Update transcript and content enrollment with completion within the Gnosis API for newly added course completion',
        self::__FS_TICKET_NUMBER__                  => 'FS #',
	    
    // CLI
        self::__CRON_EVENT__                           => 'Cron Event Task: ',
	    
    // CLI
        self::__CLI_RUN__                           => 'CLI Run: ',
	    
    // Certificate
        self::__UPDATE_COURSE_CERTIFICATE_UI__      => 'Update certificate for course ',
	    
    // Take over user
        self::__TAKE_OVER_USER__                    => 'TAKE OVER: ',
	    
    // Cloud CME degree & credit category update
        self::__CLOUD_CME_DEGREE_CE_CREDIT_UPDATE__ => 'Cloud CME update user degree and ce credit category id'
	];
	
	static protected $action_log_msg = array();
	
	/**
	 * get the message text
	 */
	static public function getActionLogMsg(){
		return join(' ~(:> ', self::$action_log_msg);
	}
	
	/**
	 * Add msg to existing log msgs
	 * @param string $msg_code
	 */
	public static function addMsg($msg_code) {
	    self::$action_log_msg[] = self::getActionLogMsgWithCode($msg_code);;
	}
	
	/**
	 * Add dynamic action log msg to existing log msgs - holly
	 * @param string $msg_code
	 * @param string $dynamic_msg
	 */
	public static function addMsgWithDynamic($msg_code, $dynamic_msg) {
	    self::$action_log_msg[] = self::getActionLogMsgWithCode($msg_code, $dynamic_msg);
	}
	
    /**
     * Get action log msg by code - holly
     * @param string $msg_code
     * @return string $msg
     */
	private static function getActionLogMsgWithCode($msg_code, $set_msg = '') {
	    if (is_array($set_msg)) {
	        $set_msg = join('**', $set_msg);
	    }
	    
	    if (!isset(self::$msgs_codes[$msg_code])) {
	        warning("Message code: {$msg_code} has no message set for archive log.", false);
	        $msg = "ERROR - {$msg_code}";
	    } else {
	        $msg = self::$msgs_codes[$msg_code] . $set_msg;
	    }
	    
	    // check if user was taken over by admin
	    if(User_Current::id() != User_Current::pupetMasterId()) {
	        $msg = 'TAKE OVER BY ' . User_Current::pupetMasterId() . ': ' . $msg;
	    }
	     
	    return $msg;
	}

	/**
	 * Clean action log msg
	 * @author hollywu
	 */
	public static function cleanActionLogMsg() {
	    self::$action_log_msg = [];
	}
}