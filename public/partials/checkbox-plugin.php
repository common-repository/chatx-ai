<div class="chatx-clearfix"></div>
<div class="chatx-checkbox-plugin-container">
    <div
        class="chatx-checkbox-plugin chatx-checkbox-plugin-<?php echo isset($atts['id']) && $atts['id'] ? $atts['id'] : 'defaultid'; ?>"
        data-type="<?php echo $atts['type']; ?>"
        data-button-selector="<?php echo $atts['button-selector']; ?>"
        data-parent-selector="<?php echo $atts['parent-selector']; ?>"
        data-messenger_app_id="<?php echo $mess_app_id; ?>"
        data-page_id="<?php echo $page_id; ?>"
        data-origin="<?php echo $origin; ?>"
        data-user_ref="<?php echo $user_ref; ?>">

        <div class="chatx-speech-bubble">
            <button type="button" class="chatx-modal-close">&times;</button>
            <p><?php echo $atts['bubble']; ?></p>
        </div>

        <?php if(isset($atts['customcss']) && $atts['customcss']): ?>
            <style>
                <?php echo $atts['customcss']; ?>
            </style>
        <?php endif; ?>

        <span class="chatx-circle-mark"></span>

    </div>
</div>
