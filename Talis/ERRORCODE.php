<?php
/**
 * 5**  server errors
 * **   Authentication errors
 * 1**  APP/CONTENT errors (versions/no app)
 * 
 * @author itaymoav
 *
 */
abstract class ERRORCODE{
    const   
            // ----------------------- 1 to 99 Auth, users and ORG selection
            AUTH_FAILED                     = 1,	//  user could not be authenticated
            UNKNOWN_USER                    = 2,	//  unknown user in system
            NO_ORG_SELECTED                 = 3,	//  for any action that requires organization selection
            NO_ORG_ACCESS					= 4,	//  User has no access to the current organization
            WRONG_ORG_TASK					= 5,	//  User has the task but not in the current organization

            //100-199 assets issues 
            UNKNOWN_ASSET                   = 100, //  Refers to any asset in the system. Assets is something a user owns, file,course,content,department,learner,edu grp, org etc
            NOT_OWNER_OF_ASSET	            = 101, //  Refers to any attempt to access or modify an existing asset, without the proper access.					
            NO_INFORMATION_FOUND            = 102, //  Refers to any attemt to get information on an asset that exists but information is not found.
            
            //200-299
            
            //300-399
            
            //400-499 - URLs pointing to wrong place.
            HTTPS                           = 400, //url should have https in it
            UNKNOWN_CONTROLLER              = 401, //No Such conteroller in system
            UNKNOWN_ACTION                  = 402, //Client tries to call an un-existing action (either in a controller or an API action) which does not exists.
            
            
            GENERAL                         = 500, //server error
            DATA_VALIDATION                 = 501  //general input data failed some validation.
    ;
}
