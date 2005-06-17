<?php /* Smarty version 2.6.2, created on 2004-06-25 09:17:05
         compiled from latest_news.tpl.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'nl2br', 'latest_news.tpl.html', 17, false),)), $this); ?>

      <?php if ($this->_tpl_vars['news']): ?>
      <table width="100%" bgcolor="<?php echo $this->_tpl_vars['cell_color']; ?>
" border="0" cellspacing="0" cellpadding="1" align="center">
        <tr>
          <td width="100%">
            <table bgcolor="#FFFFFF" width="100%" border="0" cellspacing="0" cellpadding="4">
              <tr>
                <td>
                  <span class="default"><b>News and Announcements</b></span>
                </td>
              </tr>
              <tr>
                <td bgcolor="<?php echo $this->_tpl_vars['light_color']; ?>
" class="default">
                  <?php if (isset($this->_sections['i'])) unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['news']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
                  <b><?php echo $this->_tpl_vars['news'][$this->_sections['i']['index']]['nws_created_date']; ?>
 - <a href="news.php?id=<?php echo $this->_tpl_vars['news'][$this->_sections['i']['index']]['nws_id']; ?>
" class="link" title="full news entry"><?php echo $this->_tpl_vars['news'][$this->_sections['i']['index']]['nws_title']; ?>
</a></b>
                  <br /><br />
                  <?php echo ((is_array($_tmp=$this->_tpl_vars['news'][$this->_sections['i']['index']]['nws_message'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); ?>

                  <br /><br />
                  <?php endfor; endif; ?>
                  <a href="news.php" class="link">Read All Notices</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <br />
      <?php endif; ?>
