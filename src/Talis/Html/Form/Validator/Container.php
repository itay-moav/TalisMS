<?php namespace Talis\Html\Form\Validator;

/**
 * A JS generator for https://github.com/nghuuphuoc/bootstrapvalidator/tree/master/demo Formvalidator
 * 
 * This class is the MAIN object of the JS Validation code generator
 * The only purpose of this Validator module is to generate the JS code
 * for the LMS current bootstrap (3.3) vaslidation, see link above.
 * 
 * The container (this) will collect each element validation rules (can do it ..future.. from a server side form element or manually when declaring each element.
 * Use the __toString method to echo the JS at the end of the page.
 * There should be a method for each validation type.
 * There is a data structure for each validation type at the bottom opf this page.
 * The assumption (convention over configuration) that the error message container is the same id as the checked element + _msg 
 * 
 * @author itay
 * @Date Oct 8th 2018
 */
class Container{
    private $form_id = '';
    
    private $validators = [];
    
    private $headers = [];
    
    public function __construct(string $form_id){
        $this->form_id = $form_id;
    }
    
    /**
     * adds to validators which is an [element name][validator type] ={config values}
     * @param string $element_name
     * @return Container
     */
    public function add_required(\Talis\Html\Form\Element\aElement $element,string $message=''):Container{
        $this->add($element);
        $this->validators[$element->name()]->validators->{Required::NAME} = new Required($message);
        return $this;
    }
    
  /**
     * initiate element in container
     * 
     * @param \Talis\Html\Form\Element\aElement $element
     */
    private function add(\Talis\Html\Form\Element\aElement $element):void{
        if(!isset($this->validators[$element->name()])){
            $this->validators[$element->name()] = \array_to_object([
                'container'  => '#' . $element->get_id() . '_msg',
                'trigger'    => 'blur',
                'validators' => null
            ]);
            $this->validators[$element->name()]->validators = new \stdClass;
        }
    }
    
    /**
     * Generate the JS header of the validation
     * @return \stdClass
     */
    private function header():\stdClass{
        $header = \array_to_object([
            'excluded'  => '',
            'message'   => 'This value is not valid',
            'live'      => 'enabled',
            'feedbackIcons' => ''
        ]);

        $header->fields        = new \stdClass;
        $header->excluded      = [];
        $header->feedbackIcons = \array_to_object([
            'valid'         => 'glyphicon glyphicon-ok',
            'invalid'       => 'glyphicon glyphicon-remove',
            'validating'    => 'glyphicon glyphicon-refresh'
        ]);
        foreach($this->headers as $name => $action){
            $header->$name = $action;
        }
        return $header;
    }
    
    /**
     * Echo JS
     */
    public function __toString(){
        return '$("#' . $this->form_id . '").bootstrapValidator(' . $this->get_json() . ');';
    }
    
    /**
     * Encodes JS
     * @return string
     */
    public function get_json():string{
        $full_js = $this->header();
        foreach($this->validators as $name => $validator){
            $full_js->fields->{$name} = $validator;
        }
        return json_encode($full_js);
    }
}






/**
 * 
 * @author itay
 *
 */
class Required{
    const NAME = 'notEmpty';
    public  $message = 'You must fill this field.';
    
    /**
     * @param string $message
     */
    public function __construct(string $message=''){
        $this->message = $message ?: $this->message;
    }
    
}



/*

		UserRegisterEmployeeMyinfo.TheForm.bootstrapValidator({
			excluded:[],
		    message: 'This value is not valid',
		    live: 'enabled',
		    feedbackIcons: {
		        valid: 'glyphicon glyphicon-ok',
		        invalid: 'glyphicon glyphicon-remove',
		        validating: 'glyphicon glyphicon-refresh'
		    },
		
		    fields: {
		    	username:{
		    		container: '.username_msg',
		    		trigger:'change',
		    		validators: {
		    			notEmpty: {
		    				message: '*Required field.'
		    			},
		    			uniqueUsername: {
		    				message: 'An account already exists for this email address. <a href="' + window.lms2BaseUrl + '/login/restore/">Reset Your Password</a>.'
		    			},
		    			notFraudEmail: {
		    				message: 'This email address is not valid'
		    			},
		    			emailAddress: {
		    				message: 'This email address is not a valid email address'
		    			}
		    		}
		    	},
		       phone: {
		            container: '.phone_msg',
		            trigger:'blur',
		            validators: {
		                   notEmpty: {
		                        message: '*Required field.'
		                   },
			               regexp: {
			                	regexp: /^[ ]*\d{3}[\s.-]*\d{3}[\s.-]*\d{4}$/,
			                	message: 'Phone xxxxxxxxxx or xxx.xxx.xxxx or xxx-xxx-xxxx'
			               }
		            } 
			    },
		    	password:{
		    		container: '.password_msg',
		        	trigger:'change',
		            validators: {
		                notEmpty: {
		                    message: '*Required field.'
		                },
		                stringLength: {
		                	message: 'Password must be between 6 and 12 characters',
		                	min: 6,
		                	max: 12
		                },
		                regexp: {
		                	regexp: /^(?=.*[0-9])(?=.*[a-zA-Z])/, //lookahead ...
		                	message: 'Password must contain letters and numbers'
		                }
		            }
		        },
		        confirm_password:{
		        	container: '.confirm_password_msg',
		        	trigger:'change',
		        	validators: {
		        		notEmpty: {
		        			message: '*Required field.'
		        		},
		        		identical: {
		        			message: 'Password and Confirm Password must be the same',
		        			field: 'password'
		        		}
		        	}
		        },
		       first_question_id:{
		    	   container: '.first_question_id_msg',
		    	   trigger:'change',
		    	   validators: {
		    		   notEmpty: {
		    			   message: '*Required field.'
		    		   },
		    		   different: {
		    			   field:'second_question_id',
		    			   message: 'Cannot use the same question twice'
		    		   }
		    	   }
		       },
		       second_question_id:{
		    	   container:'.second_question_id_msg',
		    	   trigger:'change',
		    	   validators: {
		    		   notEmpty: {
		    			   message: '*Required field.'
		    		   },
		    		   different: {
		    			   field: 'first_question_id',
		    			   message: 'Cannot use the same question twice'
		    		   }
		    	   }
		       },
		       first_question_answer:{
		    	   container:'.first_question_answer_msg',
		    	   trigger:'change',
		    	   validators:{
		    		   notEmpty: {
		    			   message: '*Required field.'
		    		   }
		    	   }
		       },
		       second_question_answer:{
		    	   container:'.second_question_answer_msg',
		    	   trigger:'change',
		    	   validators:{
		    		   notEmpty: {
		    			   message: '*Required field.'
		    		   }
		    	   }
		       }
		    }//fields
		})

 */