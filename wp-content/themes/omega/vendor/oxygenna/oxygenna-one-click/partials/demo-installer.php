<div class="wrap">
    <?php if (isset($_POST['slowinstall'])): ?>
        <input id="slowinstalloption" type="hidden" value="1">
    <?php endif ?>

    <h2><?php _e('Installing Demo Content', 'omega-admin-td'); ?></h2>
    <h4>Importing demo content, go and put the kettle on whilst we set this theme up.</h4>

    <div id="ajax-errors-here"></div>

    <table id="package-table" class="widefat">
        <thead>
            <tr>
                <th>Package</th>
                <th width="40%">Progress</th>
                <th>Status</th>
                <th>Import Job</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($packages as $package): ?>
                <?php if (in_array($package['filename'], $selected_packages)): ?>
                    <tr class="package-row" data-file="<?php echo $package['filename'] ?>">
                        <td>
                            <?php echo $package['name'] ?>
                        </td>
                        <td>
                            <div class="progress"></div>
                        </td>
                        <td>
                            <strong class="status">Imported <span class="count">0</span> out of <span class="total">0</span> items</strong>
                        </td>
                        <td class="job"></td>
                        <td><a href="#" class="toggle-details">Details</a></td>
                    </tr>
                    <tr style="display: none;">
                        <td colspan="5">
                            <table class="widefat import-list">
                                <thead>
                                    <tr>
                                        <th>Status</th>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Post Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                <?php endif ?>
            <?php endforeach ?>
        </tbody>
    </table>
    <form id="import-finished-form" method="post" action="admin.php?page=<?php echo THEME_SHORT; ?>-oneclick">
        <input type="hidden" name="one_click_status" value="finished-page">
    </form>
</div>
