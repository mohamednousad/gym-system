<?php
require_once '../../config/navigation.php';
require_user();
define('PAGE_TITLE', 'My Profile');
define('PAGE_SUB', 'Update your personal details');
$pdo = db();
$user = current_user();

if (is_post()) {
    verify_csrf();
    $name  = post('name');
    $phone = post('phone');
    $new_pass = $_POST['new_password'] ?? '';
    $cur_pass = $_POST['current_password'] ?? '';

    $image_path = upload_image('profile_image', $user['profile_image']);

    $data = ['name' => $name, 'phone' => $phone, 'profile_image' => $image_path, 'id' => $user['id']];

    if ($new_pass !== '') {
        if (!password_verify($cur_pass, $user['password'])) {
            flash('Current password is incorrect.', 'error');
            redirect('profile.php');
        }
        if (strlen($new_pass) < 6) {
            flash('New password must be at least 6 characters.', 'error');
            redirect('profile.php');
        }
        $data['password'] = password_hash($new_pass, PASSWORD_BCRYPT);
        $pdo->prepare('UPDATE users SET name=:name,phone=:phone,profile_image=:profile_image,password=:password WHERE id=:id')->execute($data);
    } else {
        $pdo->prepare('UPDATE users SET name=:name,phone=:phone,profile_image=:profile_image WHERE id=:id')->execute($data);
    }

    flash('Profile updated successfully.');
    redirect('profile.php');
}

$user = current_user();
include APP_ROOT . '/views/includes/head_user.php';
?>

<div class="page-header">
    <div><h2>My Profile</h2><p>Manage your account details</p></div>
</div>

<div class="grid-2" style="align-items:start;">
    <div class="card">
        <div class="card-header"><span class="card-title">Personal Information</span></div>
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div style="text-align:center;margin-bottom:20px;">
                <?php if ($user['profile_image']): ?>
                    <img src="<?= e(img_url($user['profile_image'])) ?>"
                         onclick="showImageModal('<?= e(img_url($user['profile_image'])) ?>')"
                         style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid var(--primary);cursor:zoom-in;margin:0 auto;">
                <?php else: ?>
                    <?= avatar($user['name'], null, 90) ?>
                <?php endif; ?>
                <div style="margin-top:10px;">
                    <label class="btn btn-secondary btn-sm" style="cursor:pointer;">
                        Upload
                        <input type="file" name="profile_image" accept="image/*" style="display:none;" onchange="previewImage(this)">
                    </label>
                </div>
                <img id="imgPreview" src="" style="display:none;width:80px;height:80px;border-radius:50%;object-fit:cover;margin:10px auto 0;border:2px solid var(--primary);">
            </div>

            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input name="name" class="form-control" value="<?= e($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input class="form-control" value="<?= e($user['email']) ?>" disabled style="opacity:0.5;">
                <span class="form-hint">Email cannot be changed</span>
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" placeholder="+94 77 000 0000">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Save Changes</button>
        </form>
    </div>

    <div>
        <div class="card" style="margin-bottom:18px;">
            <div class="card-header"><span class="card-title">Change Password</span></div>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="name" value="<?= e($user['name']) ?>">
                <input type="hidden" name="phone" value="<?= e($user['phone'] ?? '') ?>">
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" placeholder="Enter current password">
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters">
                </div>
                <button type="submit" class="btn btn-warning" style="width:100%;">Update Password</button>
            </form>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Membership Info</span></div>
            <div>
                <?php foreach ([
                    'Plan'    => $user['membership_plan'] ?? 'None',
                    'Status'  => null,
                    'Renewal' => $user['renewal_date'] ?? '—',
                    'Joined'  => date('d M Y', strtotime($user['created_at'])),
                ] as $lbl => $val): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border);font-size:13px;">
                    <span class="text-muted"><?= $lbl ?></span>
                    <?php if ($lbl === 'Status'): ?>
                        <?= badge_status($user['membership_status'] ?? 'pending') ?>
                    <?php else: ?>
                        <strong><?= e($val) ?></strong>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:14px;">
                <a href="/gym-system/views/user/membership.php" class="btn btn-primary btn-sm" style="width:100%;">Manage Membership</a>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var prev = document.getElementById('imgPreview');
            prev.src = e.target.result;
            prev.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<?php include APP_ROOT . '/views/includes/foot_user.php'; ?>
