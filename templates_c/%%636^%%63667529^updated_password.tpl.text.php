<?php /* Smarty version 2.6.2, created on 2004-06-25 13:52:45
         compiled from notifications/updated_password.tpl.text */ ?>
Your user account password has been updated in <?php echo $this->_tpl_vars['app_title']; ?>
.

Your account information as it now exists appears below.

----------------------------------------------------------------------
        Full Name: <?php echo $this->_tpl_vars['user']['usr_full_name']; ?>

    Email Address: <?php echo $this->_tpl_vars['user']['usr_email']; ?>
 
         Password: <?php echo $this->_tpl_vars['user']['usr_password']; ?>

             Role: <?php echo $this->_tpl_vars['user']['role']; ?>

         Projects: <?php echo $this->_tpl_vars['user']['projects']; ?>

----------------------------------------------------------------------