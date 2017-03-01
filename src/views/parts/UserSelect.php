<?php
namespace ellsif;
/**
 * ユーザー選択リストを表示
 */
$_part = loadPart('UserSelect');
$_users = [];
if ($_part) {
  $_pageData = $_part->getData();
  if ($_pageData && isset($_pageData['users'])) {
    $_users = $_pageData['users'];
  }
}
?>
<div class="user-select">
  <?php foreach($_users as $user) : ?>
    <div class="checkbox">
      <label>
        <input name="user_id[]" type="checkbox" value="<?php echo $user['id'] ?>"><?php echo $user['name'] ?>（<?php echo $user['userId'] ?>）
      </label>
    </div>
  <?php endforeach; ?>
</div>
