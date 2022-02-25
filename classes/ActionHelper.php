<?php

use Rybel\backbone\Helper;

class ActionHelper extends Helper
{
    public function getContactActions($contact_id) {
        return $this->query("SELECT Actions.action_id, Actions.title, Actions.date, Contacts.name, Contacts.contact_id FROM Actions, Contacts WHERE Actions.contact_id = Contacts.contact_id AND Contacts.contact_id = ?", $contact_id);
    }

    public function getUserActions($user_id) {
        return $this->query("SELECT Actions.action_id, Actions.title, Actions.date, Contacts.name, Contacts.contact_id FROM Actions, Contacts WHERE Actions.contact_id = Contacts.contact_id AND Contacts.user_id = ?", $user_id);
    }

    public function getAction($action_id) {
        return $this->query("SELECT Actions.action_id, Actions.title, Actions.date, Contacts.name, Contacts.contact_id FROM Actions, Contacts WHERE Actions.contact_id = Contacts.contact_id AND Actions.action_id = ? LIMIT 1", $action_id);
    }

    public function deleteAction($action_id) {
        return $this->query("DELETE FROM Actions WHERE action_id = ?", $action_id);
    }

    public function convertAction($action_id) {
        $actionData = $this->getAction($action_id);

        $data = array();
        $data['last_contact'] = $actionData['date'];
        $data['last_contact_details'] = $actionData['title'];
        $data['contact_id'] = $actionData['contact_id'];

        $contactHelper = new ContactHelper($this->config);
        if (!$contactHelper->updateLastContact($data)) {
            return false;
        }
        return $this->deleteAction($action_id);
    }

    public function createAction($data) {
        return $this->query("INSERT INTO Actions (contact_id, title, date) VALUES (?, ?, ?)", $data['contact_id'], $data['title'], $data['date']);
    }

    public function render_contactActions($contact_id) {
        $actions = $this->getContactActions($contact_id);
        if (count($actions) > 0) {
            ?>
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Date</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($actions as $action) {
                    echo '<tr>';
                    echo '<td>' . $action['name'] . '</td>';
                    echo '<td>' . date('m/d/Y', strtotime($action['date'])) . '</td>';
                    echo '<td><a href="?contact_id=' . $contact_id . '&convert_action=' . $action['action_id'] . '">✅</td>';
                    echo '<td><a href="?contact_id=' . $contact_id . '&delete_action=' . $action['action_id'] . '">🗑</td>';
                    echo '</tr>';
                } ?>
                </tbody>
            </table>
            <?php
        } else {
            echo 'No actions found';
        }
    }
}
