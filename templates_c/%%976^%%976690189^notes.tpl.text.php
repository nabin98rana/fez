<?php /* Smarty version 2.6.2, created on 2004-07-07 14:42:13
         compiled from notifications/notes.tpl.text */ ?>
<?php echo $this->_tpl_vars['data']['note']['not_note']; ?>



----------------------------------------------------------------------
These are the current issue details:
----------------------------------------------------------------------
               ID: <?php echo $this->_tpl_vars['data']['iss_id']; ?>

          Summary: <?php echo $this->_tpl_vars['data']['iss_summary']; ?>

           Status: <?php echo $this->_tpl_vars['data']['sta_title']; ?>

          Project: <?php echo $this->_tpl_vars['data']['prj_title']; ?>

      Reported By: <?php echo $this->_tpl_vars['data']['usr_full_name']; ?>

         Priority: <?php echo $this->_tpl_vars['data']['pri_title']; ?>

      Description:
----------------------------------------------------------------------
<?php echo $this->_tpl_vars['data']['iss_description']; ?>

----------------------------------------------------------------------

To view more details of this issue, or to update it, please visit the 
following URL:
<?php echo $this->_tpl_vars['app_base_url']; ?>
view.php?id=<?php echo $this->_tpl_vars['data']['iss_id']; ?>


Please Note: If you do not wish to receive any future email 
notifications from <?php echo $this->_tpl_vars['app_title']; ?>
, please change your account preferences by 
visiting the URL below:
<?php echo $this->_tpl_vars['app_base_url']; ?>
preferences.php
