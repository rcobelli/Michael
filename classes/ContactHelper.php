<?php

use Google\Service\PeopleService\Person;
use Rybel\backbone\Helper;

class ContactHelper extends Helper
{
    /**
     * @param $person Person
     * @return bool
     * @throws Exception
     */
    public function importContact(Person $person): bool
    {
        if (count($person->getNames()) != 0) {
            $name = $person->getNames()[0]->getDisplayName();

            $google_id = $person->getNames()[0]->getMetadata()->getSource()->getId();

            $linkedin = null;
            if (count($person->getUrls()) != 0) {
                foreach ($person->getUrls() as $url) {
                    if ($url->getFormattedType() == "Linkedin") {
                        $linkedin = $url->getValue();
                    }
                }
            }

            $birthday_day = null;
            $birthday_month = null;
            if (count($person->getBirthdays()) != 0) {
                $birthday_month = $person->getBirthdays()[0]->getDate()->getMonth();
                $birthday_day = $person->getBirthdays()[0]->getDate()->getDay();
            }

            $company = null;
            if (count($person->getOrganizations()) != 0) {
                $company = $person->getOrganizations()[0]->getName();
            }

            // Check if the user hasn't changed
            $query = $this->query("SELECT * FROM Contacts WHERE google_id = ? LIMIT 1", $google_id);
            if (!empty($query)) {
                if ($query['name'] == $name && $query['relation_detail'] == $company && $query['linkedin'] ==  $linkedin
                    && $query['birthday_day'] == $birthday_day && $query['birthday_month'] == $birthday_month) {
                    return true;
                }
            }

            $result = $this->query("UPDATE Contacts SET `new` = 1, `linkedin` = ?, birthday_day = ?, birthday_month = ?, relation_detail = ? WHERE google_id = ?",
                $linkedin, $birthday_day, $birthday_month, $company, $google_id
            );
            if ($result === false) {
                throw new Exception($this->getErrorMessage());
            }
            return true;
        }
        return false;
    }

    /**
     * @return void
     * @throws \Google\Exception
     */
    public function syncWithGoogle() {
        $loginHelper = new LoginHelper($this->config);
        $client = $loginHelper->getValidatedClient();
        $service = new Google_Service_PeopleService($client);

        $optParams = array(
            'pageSize' => 1000,
            'personFields' => 'names,emailAddresses,birthdays,organizations,urls',
        );
        $results = $service->people_connections->listPeopleConnections('people/me', $optParams);

        foreach ($results->getConnections() as $person) {
            $this->importContact($person);
        }
    }

    /**
     * @param string $userID
     * @return array
     */
    public function getContactsStatusInfo(string $userID): array {
        return $this->query("SELECT contact_id, Contacts.name, last_contact, last_contact_details, relation, relation_detail, Contacts.tier_id, Tiers.name as tier_name, linkedin, linkedin_last_check, birthday_day, birthday_month, new, ABS(DATEDIFF(last_contact, NOW())) as contact_gap, Tiers.greenDays, Tiers.YellowDays, Tiers.redDays FROM Contacts, Tiers WHERE Contacts.tier_id = Tiers.tier_id AND last_contact IS NOT NULL AND user_id = ? ORDER BY Contacts.name", $userID);
    }

    /**
     * @param string $userID
     * @return array
     */
    public function getContacts(string $userID): array {
        return $this->query("SELECT contact_id, Contacts.name, last_contact, last_contact_details, relation, relation_detail, Contacts.tier_id, Tiers.name as tier_name, linkedin, linkedin_last_check, birthday_day, birthday_month, new, ABS(DATEDIFF(last_contact, NOW())) as contact_gap FROM Contacts, Tiers WHERE Contacts.tier_id = Tiers.tier_id AND user_id = ? ORDER BY Contacts.name", $userID);
    }

    /**
     * @param string $userID
     * @return array
     */
    public function getNewContacts(string $userID): array {
        return $this->query("SELECT contact_id, Contacts.name, last_contact, last_contact_details, relation, relation_detail, Contacts.tier_id, Tiers.name as tier_name, linkedin, linkedin_last_check, birthday_day, birthday_month, new, ABS(DATEDIFF(last_contact, NOW())) as contact_gap FROM Contacts, Tiers WHERE Contacts.tier_id = Tiers.tier_id AND new = 1 AND user_id = ? ORDER BY Contacts.name", $userID);
    }

    /**
     * @param string $userID
     * @return array
     */
    public function getStaleLinkedinContacts(string $userID): array {
        return $this->query("SELECT contact_id, Contacts.name, last_contact, last_contact_details, relation, relation_detail, Contacts.tier_id, Tiers.name as tier_name, linkedin, linkedin_last_check, birthday_day, birthday_month, new, ABS(DATEDIFF(last_contact, NOW())) as contact_gap FROM Contacts, Tiers WHERE Contacts.tier_id = Tiers.tier_id AND (ABS(DATEDIFF(linkedin_last_check, NOW())) >= 90 OR (linkedin_last_check IS NULL AND linkedin IS NOT NULL)) AND user_id = ? ORDER BY Contacts.name", $userID);
    }

    /**
     * @param string $userID
     * @param string $contactID
     * @return array
     */
    public function getContact(string $userID, string $contactID): array {
        return $this->query("SELECT contact_id, Contacts.name, last_contact, last_contact_details, relation, relation_detail, Contacts.tier_id, Tiers.name as tier_name, linkedin, linkedin_last_check, birthday_day, birthday_month, new, ABS(DATEDIFF(last_contact, NOW())) as contact_gap FROM Contacts, Tiers WHERE Contacts.tier_id = Tiers.tier_id AND user_id = ? AND contact_id = ? LIMIT 1", $userID, $contactID);
    }

    /**
     * @return array
     */
    public function getTiers(): array {
        return $this->query("SELECT * FROM Tiers;");
    }

    /**
     * @param $contact_id
     * @return bool
     */
    public function updateLinkedinCheck($contact_id): bool {
        return $this->query("UPDATE Contacts SET linkedin_last_check = NOW() WHERE contact_id = ?", $contact_id);
    }

    /**
     * @param $gap
     * @param $tier_id
     * @return int
     */
    public function getColorCode($gap, $tier_id): int {
        if (is_null($gap)) return -1;
        $result = $this->query("SELECT * FROM Tiers WHERE tier_id = ? LIMIT 1", $tier_id);
        if ($gap <= $result['greenDays']) return 1;
        if ($gap <= $result['yellowDays']) return 2;
        if ($gap <= $result['redDays']) return 3;
        return 4;
    }

    /**
     * @param $gap
     * @param $tier_id
     * @return string
     */
    public function getColorEmoji($gap, $tier_id): string {
        if (is_null($gap)) return "❌";
        $result = $this->query("SELECT * FROM Tiers WHERE tier_id = ? LIMIT 1", $tier_id);
        if ($gap < $result['greenDays']) return "🟢️";
        if ($gap < $result['yellowDays']) return "🟡";
        if ($gap < $result['redDays']) return "🔴";
        return "‼";
    }

    /**
     * @param $tier_id
     * @return array
     */
    public function getTierDates($tier_id): array {
        return $this->query("SELECT * FROM Tiers WHERE tier_id = ? LIMIT 1", $tier_id);
    }

    /**
     * @param $data
     * @return bool
     */
    public function updateContact($data): bool {
        if ($data['last_contact'] == "") {
            $data['last_contact'] = null;
        }

        if ($data['last_contact_details'] == "") {
            $data['last_contact_details'] = null;
        }
        return $this->query("UPDATE Contacts SET relation = ?, tier_id = ?, last_contact = ?, last_contact_details = ? WHERE user_id = ? AND contact_id = ?", $data['relation'], $data['tier'], $data['last_contact'], $data['last_contact_details'], $data['user_id'], $data['contact_id']);
    }

    /**
     * @param $data
     * @return bool
     */
    public function updateLastContact($data): bool {
        return $this->query("UPDATE Contacts SET last_contact = ?, last_contact_details = ? WHERE contact_id = ?", $data['last_contact'], $data['last_contact_details'], $data['contact_id']);
    }

    /**
     * @param $contact_id
     * @return bool
     */
    public function viewContact($contact_id): bool {
        return $this->query("UPDATE Contacts SET new = 0 WHERE contact_id = ?", $contact_id);
    }

    /**
     * @return void
     */
    public function render_mainTable() {
        $contacts = $this->getContacts($_SESSION['id']);
        if (count($contacts) > 0) {
            ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Relation</th>
                        <th>Tier</th>
                        <th>Status</th>
                        <th>Last Contact</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($contacts as $contact) {
                        echo '<tr class="clickable-row" data-href="contact.php?contact_id=' . $contact['contact_id'] . '">';
                        echo '<td>' . ($contact['new'] ? "🌟" : "") . $contact['name'] . '</td>';
                        echo '<td>' . $contact['relation'] . '</td>';
                        echo '<td>' . $contact['tier_name'] . '</td>';
                        echo '<td>' . $this->getColorEmoji($contact['contact_gap'], $contact['tier_id']) . '</td>';
                        echo '<td>' . (is_null($contact['last_contact']) ? "" : date('m/d/Y', strtotime($contact['last_contact']))) . '</td>';
                        echo '</tr>';
                    } ?>
                </tbody>
            </table>
            <?php
        } else {
            echo 'No contacts found';
        }
    }
}
