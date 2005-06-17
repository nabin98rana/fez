<?php /* Smarty version 2.6.2, created on 2005-02-07 09:25:58
         compiled from notifications/files.tpl.text */ ?>
This is an automated message sent at your request from <?php echo $this->_tpl_vars['app_title']; ?>
.

To view more details of this issue, or to update it, please visit the 
following URL:
<?php echo $this->_tpl_vars['app_base_url']; ?>
view.php?id=<?php echo $this->_tpl_vars['data']['iss_id']; ?>


New Attachment:
----------------------------------------------------------------------
      Owner: <?php echo $this->_tpl_vars['data']['attachment']['usr_full_name']; ?>

       Date: <?php echo $this->_tpl_vars['data']['attachment']['iat_created_date']; ?>

      Files:
<?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['data']['attachment']['files']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
             <?php echo $this->_tpl_vars['data']['attachment']['files'][$this->_sections['i']['index']]['iaf_filename']; ?>
 (<?php echo $this->_tpl_vars['data']['attachment']['files'][$this->_sections['i']['index']]['iaf_filesize']; ?>
)             
<?php endfor; endif; ?>
Description:
----------------------------------------------------------------------
<?php echo $this->_tpl_vars['data']['attachment']['iat_description']; ?>

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

Please Note: If you do not wish to receive any future email 
notifications from <?php echo $this->_tpl_vars['app_title']; ?>
, please change your account preferences by 
visiting the URL below:
<?php echo $this->_tpl_vars['app_base_url']; ?>
preferences.php
