<?php

/**
 * @file
 * A form to collect an email address for RSVP details.
 */

 namespace Drupal\rsvplist\Form;

 use Drupal\Core\Form\FormBase;
 use Drupal\Core\Form\FormStateInterface;

 class RSVPForm extends FormBase {

    /**
     * {@inheritdoc}
     */
     public function getFormId() {
         return 'rsvplist_email_form';
     }

     /**
      * {@inheritdoc}
      */
      public function buildForm(array $form, FormStateInterface $form_state) {

          //Get the fully loaded node object
          $node = \Drupal::routeMatch()->getParameter('node');

          if( !(is_null($node)) ) {
              $nid = $node->id();
          }
          else {
              $nid = 0;
          }

          //Establish form render array. It has an email text field, 
          //a submit button and a hidden field for nid.
          $form['email'] = [
              '#type' => 'textfield',
              '#title' => t('Email address'),
              '#size' => 25,
              '#description' => t("Submit this to receive updates from us!!"),
              '#required' => TRUE,
          ];

          $form['submit'] = [
              '#type' => 'submit',
              '#value' => t('Submit'),
          ];
          $form['nid'] = [
              '#type' => 'hidden',
              '#value' => $nid,
          ];

          return $form;
      }
      
      /**
       * {@inheritdoc}
       */
      public function validateForm(array &$form, FormStateInterface $form_state) {
        $email_value = $form_state->getValue('email');
        if( !(\Drupal::service('email.validator')->isValid($email_value))) {
          $form_state->setErrorByName('email',
            $this->t('It appears that %mail is not a valid email, please try again', ['%mail' => $email_value])
        );
        }
      }

      /**
       * {@inheritdoc}
       */
      public function submitForm(array &$form, FormStateInterface $form_state){
          //$submitted_email = $form_state->getValue('email');
          //$this->messenger()->addMessage(t("Thank you, you entered @entry", ['@entry' => $submitted_email]));
        try {
            //Phase 1 - Gather all the data
            $uid = \Drupal::currentUser()->id();
            //full user - just for info
            $full_user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());

            //obtain values entered into the form
            $nid = $form_state->getValue('nid');
            $email = $form_state->getValue('email');

            //We can use php current time or drupal's current time
            $current_time = \Drupal::time()->getRequestTime();

            //Phase 2 - Dynamic query building

            $query = \Drupal::database()->insert('rsvplist');
            $query->fields([
                'uid',
                'nid',
                'mail',
                'created'
            ]);
            $query->values([
                $uid,
                $nid,
                $email,
                $current_time
            ]);

            $query->execute();

            //Phase 3 - Print success message
            \Drupal::messenger()->addMessage(
              t('Thank you for your RSVP, you are on the list for the event.')
            );
        }
        catch(\Exception $e) {

        }
        
      }


 }