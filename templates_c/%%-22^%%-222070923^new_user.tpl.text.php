<?php /* Smarty version 2.6.2, created on 2004-07-01 14:57:16
         compiled from notifications/new_user.tpl.text */ ?>
A new user was just created for you in the system.

To start using the system, please load the URL below:
<?php echo $this->_tpl_vars['app_base_url']; ?>
index.php

----------------------------------------------------------------------
        Full Name: <?php echo $this->_tpl_vars['user']['usr_full_name']; ?>

    Email Address: <?php echo $this->_tpl_vars['user']['usr_email']; ?>
 
             Role: <?php echo $this->_tpl_vars['user']['role']; ?>

----------------------------------------------------------------------
