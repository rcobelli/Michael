<?php

use Google\Service\PeopleService\Person;
use Rybel\backbone\Helper;

class CompanyHelper extends Helper
{
    /**
     * @return array|bool
     */
    public function getCompanies() {
        return $this->query("SELECT * FROM Companies");
    }

    /**
     * @param $company_id
     * @return array|bool
     */
    public function getCompany($company_id) {
        return $this->query("SELECT * FROM Companies WHERE company_id = ? LIMIT 1", $company_id);
    }

    /**
     * @param $contact_id
     * @return array
     */
    public function getContactJobs($contact_id): array {
        return $this->query("SELECT Jobs.title, Companies.name, Companies.company_id FROM Companies, Jobs WHERE Jobs.company_id = Companies.company_id AND Jobs.contact_id = ?", $contact_id);
    }

    /**
     * @param $company_id
     * @return array
     */
    public function getCompanyContacts($company_id): array {
        return $this->query("SELECT Jobs.title, Jobs.contact_id, Contacts.name FROM Companies, Jobs, Contacts WHERE Contacts.contact_id = Jobs.contact_id AND Jobs.company_id = Companies.company_id AND Jobs.company_id = ?", $company_id);
    }

    /**
     * @param $job_id
     * @return bool
     */
    public function deleteJob($job_id): bool {
        return $this->query("DELETE FROM Jobs WHERE job_id = ?", $job_id);
    }

    /**
     * @param $data
     * @return bool
     */
    public function createJob($data): bool {
        if (!$this->createCompany($data['company'])) {
            return false;
        }

        $company_id = $this->query("SELECT company_id FROM Companies WHERE name = ? LIMIT 1", $data['company'])['company_id'];

        return $this->query("INSERT INTO Jobs (contact_id, company_id, title) VALUES (?, ?, ?)", $data['contact_id'], $company_id, $data['title']);
    }

    /**
     * @param $name
     * @return bool
     */
    public function createCompany($name): bool {
        return $this->query("INSERT IGNORE INTO Companies (name) VALUES (?)", $name);
    }

    /**
     * @param $contact_id
     * @return void
     */
    public function render_contactJobs($contact_id) {
        $companies = $this->getContactJobs($contact_id);
        if (count($companies) > 0) {
            ?>
            <table class="table table-hover">
                <tbody>
                <?php
                foreach ($companies as $company) {
                    echo '<tr class="clickable-row" data-href="company.php?company_id=' . $company['company_id'] . '">';
                    echo '<td>' . $company['name'] . '</td>';
                    echo '<td>' . $company['title'] . '</td>';
                    echo '</tr>';
                } ?>
                </tbody>
            </table>
            <?php
        } else {
            echo 'No companies found';
        }
    }

    /**
     * @return void
     */
    public function render_companies() {
        $companies = $this->getCompanies();
        if (count($companies) > 0) {
            ?>
            <table class="table table-hover">
                <tbody>
                <?php
                foreach ($companies as $company) {
                    echo '<tr class="clickable-row" data-href="company.php?company_id=' . $company['company_id'] . '">';
                    echo '<td>' . $company['name'] . '</td>';
                    echo '</tr>';
                } ?>
                </tbody>
            </table>
            <?php
        } else {
            echo 'No companies found';
        }
    }

    /**
     * @param $company_id
     * @return void
     */
    public function render_companyContacts($company_id) {
        $companies = $this->getCompanyContacts($company_id);
        if (count($companies) > 0) {
            ?>
            <table class="table table-hover">
                <tbody>
                <?php
                foreach ($companies as $company) {
                    echo '<tr class="clickable-row" data-href="contact.php?contact_id=' . $company['contact_id'] . '">';
                    echo '<td>' . $company['name'] . '</td>';
                    echo '<td>' . $company['title'] . '</td>';
                    echo '</tr>';
                } ?>
                </tbody>
            </table>
            <?php
        } else {
            echo 'No companies found';
        }
    }
}
