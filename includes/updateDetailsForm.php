<form method="post">
    <input type="hidden" value="updateDetails" name="action">
    <input type="hidden" value="<?php echo $_GET['contact_id']; ?>" name="contact_id">
    <input type="hidden" value="<?php echo $_SESSION['id']; ?>" name="user_id">
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="Relation" title="The type of contact this is" class="tooltip-enabled">Relation:</label>
            <select class="custom-select" id="Relation" name="relation" required>
                <?php
                if (is_null($contactData['relation'])) {
                    echo "<option selected disabled>Select relation...</option>";
                }
                ?>
                <option <?php echo ($contactData['relation'] == 'Social' ? 'selected' : ""); ?>>Social</option>
                <option <?php echo ($contactData['relation'] == 'Professional' ? 'selected' : ""); ?>>Professional</option>
            </select>
        </div>
        <div class="form-group col-md-6">
            <label for="tier" title="The aspirational level of relevancy" class="tooltip-enabled">Tier:</label>
            <select class="custom-select" id="tier" name="tier" required>
                <?php
                $tiers = $contactHelper->getTiers();
                foreach ($tiers as $tier) {
                    echo '<option value="' . $tier['tier_id'] . '"' . ($contactData['tier_id'] == $tier['tier_id'] ? "selected" : "") . '>' . $tier['name'] . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-3">
            <label for="lastContact">Last Contact Date:</label>
            <input type="date" id="lastContact" class="form-control" placeholder="01/02/2022" name="last_contact" value="<?php echo $contactData['last_contact'];?>">
        </div>
        <div class="form-group col-md-9">
            <label for="lastContactDetail">Last Contact Details:</label>
            <textarea class="form-control" id="lastContactDetail" rows="2" name="last_contact_details"><?php echo $contactData['last_contact_details'];?></textarea>
        </div>
    </div>
    <div class="form-row">
        <div class="col text-center">
            <button type="submit" class="btn btn-primary mt-3">Save</button>
        </div>
    </div>
</form>