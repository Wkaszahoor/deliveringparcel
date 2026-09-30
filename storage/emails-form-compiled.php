<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><?php echo e($template->exists ? 'Edit' : 'New'); ?> email template</h3></div>
            <div class="card-body">
                <form method="POST" action="<?php echo e($template->exists ? route('admin.emails.update', $template) : route('admin.emails.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php if($template->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

                    <div class="form-group">
                        <label>Key (machine name)</label>
                        <input type="text" name="key" class="form-control <?php $__errorArgs = ['key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" list="dp-email-keys"
                               value="<?php echo e(old('key', $template->key)); ?>" required placeholder="welcome_email">
                        <datalist id="dp-email-keys">
                            <?php $__currentLoopData = \App\Models\EmailTemplate::KNOWN_KEYS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($k); ?>"><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </datalist>
                        <small class="form-text text-muted">Must match the key the sending flow uses (list above). Deleting/disabling the row falls back to the built-in email.</small>
                        <?php $__errorArgs = ['key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="invalid-feedback"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label>Admin label</label>
                        <input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('name', $template->name)); ?>" required>
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="invalid-feedback"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__currentLoopData = \App\Models\EmailTemplate::CATEGORIES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cKey => $cLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cKey); ?>" <?php echo e(old('category', $template->category) === $cKey ? 'selected' : ''); ?>><?php echo e($cLabel); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['category'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="invalid-feedback"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" class="form-control <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('subject', $template->subject)); ?>" required>
                        <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="invalid-feedback"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label>Body (HTML allowed; each line = paragraph when no HTML)</label>
                        <textarea name="body" rows="12" class="form-control <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required><?php echo e(old('body', $template->body)); ?></textarea>
                        <small class="form-text text-muted">Placeholders: <?php echo e('{{name); ?> <?php echo e(email); ?> <?php echo e(order_id); ?> <?php echo e(order_ref); ?> <?php echo e(link); ?>' }} + any fields the flow passes (see help text below).</small>
                        <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="invalid-feedback"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group">
                        <label>Placeholder help (shown to future admins)</label>
                        <textarea name="placeholder_help" rows="2" class="form-control"><?php echo e(old('placeholder_help', $template->placeholder_help)); ?></textarea>
                    </div>

                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="is_enabled" name="is_enabled" value="1" <?php echo e(old('is_enabled', $template->is_enabled ?? true) ? 'checked' : ''); ?>>
                        <label class="custom-control-label" for="is_enabled">Enabled (unchecked = flow uses the built-in email)</label>
                    </div>

                    <button type="submit" class="btn btn-primary"><?php echo e($template->exists ? 'Save changes' : 'Create template'); ?></button>
                    <a href="<?php echo e(route('admin.emails.index')); ?>" class="btn btn-default border">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">How sending works</h3></div>
            <div class="card-body small">
                <ol>
                    <li>Master switch (Settings → Email) must be ON.</li>
                    <li>This template must be enabled.</li>
                    <li>Body placeholders are replaced per recipient.</li>
                    <li>Sent inline, or queued on the <code>emails</code> queue when queued sending is ON (drained by the cPanel cron <code>dp:queue-drain</code>).</li>
                    <li>Every attempt is recorded in Email Logs with status + error; failures never break the business flow.</li>
                </ol>
            </div>
        </div>
    </div>
</div>
