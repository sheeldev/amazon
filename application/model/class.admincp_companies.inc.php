<?php

class admincp_companies extends admincp
{
    /**
     * Function to delete single or multiple companies 
     *
     * @param
     *            array array with companies ids
     *
     * @return string Returns array with success and error responses where applicable
     */
    function changestatus($ids = array(), $status)
    {
        $allerrors = $successids = $failedids = $action = $display =  '';
        $count = 0;
        if ($status == 'deleted') {
            $action = '{_deleted}';
            $display = '{_deleted}';

        } else if ($status == 'suspended') {
            $action = '{_suspended}';
            $display = '{_suspended}';

        } else if ($status == 'canceled') {
            $action = '{_canceled}';
            $display = '{_canceled}';

        } else if ($status == 'banned') {
            $action = '{_banned}';
            $display = '{_banned}';

        } else if ($status == 'active') {
            $action = '{_active}';
            $display = '{_active}';

        } 
        foreach ($ids as $inc => $companyid) {
            $response = $this->dostatuschange($companyid, $action, true, $status);
            if ($response === true) {
                $successids .= "$companyid~";
                $count++;
            } else {
                $failedids .= "$companyid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = $display;
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_company}' : '{_companies}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'companies Accepted successfully' : 'Failure accepting companies'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array(
            'success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''),
            'errors' => $allerrors,
            'successids' => $successids,
            'failedids' => $failedids
        );
    }

    function dostatuschange($companyid = '', $action = '', $sendemail = true, $status)
    {
        $sql = $this->sheel->db->query("
			SELECT company_id, companyname
			FROM " . DB_PREFIX . "companies
				WHERE company_id = '" . $companyid . "'
			LIMIT 1
		");
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);

            $this->sheel->db->query("
					UPDATE " . DB_PREFIX . "companies
					SET status = '". $status ."'
					WHERE company_id = '" . $this->sheel->db->escape_string($companyid) . "'
				");
            $sql2 = $this->sheel->db->query("
					SELECT u.user_id, u.email, u.username
					FROM " . DB_PREFIX . "users u
						WHERE u.companyid = '" . $this->sheel->db->escape_string($res['company_id']) . "'
				");

            if ($sendemail and $this->sheel->db->num_rows($sql2) > 0) {
                while ($customer = $this->sheel->db->fetch_array($sql2, DB_ASSOC)) {
                    $existing = array(
                        '{{customer}}' => $customer['username'],
                        '{{oid}}' => $res['companyname'],
                        '{{reason}}' => $action
                    );
                    $this->sheel->email->mail = $customer['email'];
                    $this->sheel->email->slng = $this->sheel->language->fetch_user_slng($customer['user_id']);
                    $this->sheel->email->get('company_status_change');
                    $this->sheel->email->set($existing);
                    $this->sheel->email->send();
                }
            }
            return true;
        }
        return "Company #$companyid is not in active status";
    }
    function construct_new_company($projoctid = '0', $cid = '0', $vehicleregistration = '', $vehiclenumber = '', $make = '', $model = '', $year = '0', $value = '0', $currencyid = '0', $dateadded = '', $status = 'registered', $condition = 'excellent', $usedfor = '0', $usedforunit = 'km', $storeid = '0', $customerid = '0', $shippingpid = '0')
    {
        $this->sheel->db->query("INSERT INTO " . DB_PREFIX . "companies
        (company_id,project_id,cid,vehicle_registration,vehicle_c_number,vehicle_make,vehicle_model,vehicle_year,vehicle_value,value_currency,date_added," . DB_PREFIX . "companies.status," . DB_PREFIX . "companies.condition,used_for,used_for_unit,storeid,customer_id,shipping_profile_id)
        VALUES (
        NULL,
        '" . $this->sheel->db->escape_string($projoctid) . "',
        '" . $this->sheel->db->escape_string($cid) . "',
        '" . $this->sheel->db->escape_string($vehicleregistration) . "',
        '" . $this->sheel->db->escape_string($vehiclenumber) . "',
        '" . $this->sheel->db->escape_string($make) . "',
        '" . $this->sheel->db->escape_string($model) . "',
        '" . $this->sheel->db->escape_string($year) . "',
        '" . $this->sheel->db->escape_string($value) . "',
        '" . $this->sheel->db->escape_string($currencyid) . "',
        '" . $this->sheel->db->escape_string($dateadded) . "',
        '" . $this->sheel->db->escape_string($status) . "',
        '" . $this->sheel->db->escape_string($condition) . "',
        '" . $this->sheel->db->escape_string($usedfor) . "',
        '" . $this->sheel->db->escape_string($usedforunit) . "',
        '" . $this->sheel->db->escape_string($storeid) . "',
        '" . $this->sheel->db->escape_string($customerid) . "',
        '" . $this->sheel->db->escape_string($shippingpid) . "')
        ");

        $company_id = $this->sheel->db->insert_id();
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), "success\n" . $this->sheel->array2string($this->sheel->GPC), 'company created successfully', "A new company With Project ID: '$projoctid' was created successfully.");
        return $company_id;
    }

    function get_user_id($rid)
    {
        $uid = 0;
        $sql = $this->sheel->db->query("
				SELECT customer_id
				FROM " . DB_PREFIX . "companies
				WHERE company_id = '" . $rid . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            $uid = $res['customer_id'];
        } else {
            $uid = 0;
        }
        return $uid;
    }

    private function validatelisting($itemid = 0)
    {
        $sql = $this->sheel->db->query("
            SELECT user_id, cid, currencyid, crypto_currentprice, currentprice, crypto_startprice, startprice, project_details, project_title, date_starts, date_end, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(date_added) AS seconds, status, visible, reserve, filtered_auctiontype, bids
            FROM " . DB_PREFIX . "projects
            WHERE project_id = '" . intval($itemid) . "'
            ORDER BY user_id ASC
        ");
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            $res['iscrypto'] = ((isset($this->sheel->currency->currencies[$res['currencyid']]['iscrypto']) and $this->sheel->currency->currencies[$res['currencyid']]['iscrypto']) ? true : false);
            if ($res['visible']) {
                return 'Listing "' . o($res['project_title']) . ' (#' . $itemid . ')" has already been approved.';
            }
            $secondspast = $res['seconds'];
            $sqltime = $this->sheel->db->query("
                SELECT DATE_ADD('$res[date_end]', INTERVAL $secondspast SECOND) AS new_date_end
            ");
            $restime = $this->sheel->db->fetch_array($sqltime, DB_ASSOC);
            $new_date_end = $restime['new_date_end'];
            $datenow = DATETIME24H;
            if ($res['project_details'] == 'realtime') {
                if ($datenow > $res['date_starts']) {
                    $new_date_start = $datenow;
                } else {
                    $new_date_start = $res['date_starts'];
                }
            } else {
                $new_date_start = DATETIME24H;
            }
            // add seconds that have past back to the listings date_end
            $this->sheel->db->query("
                UPDATE " . DB_PREFIX . "projects
                SET date_starts = '" . $this->sheel->db->escape_string($new_date_start) . "',
                date_end = '" . $this->sheel->db->escape_string($new_date_end) . "',
                close_date = '0000-00-00 00:00:00',
                status='open',
                visible = '1'
                WHERE project_id = '" . intval($itemid) . "'
                LIMIT 1
            ");

            return true;
        }
        return 'Could not find or validate listing id #' . $itemid;
    }

    public function validate($ids = array())
    {
        $this->sellers = array();
        $allerrors = $successids = $failedids = '';
        $count = 0;
        foreach ($ids as $inc => $itemid) {
            $response = $this->validatelisting($itemid);
            if ($response === true) {
                $successids .= "$itemid~";
                $count++;
            } else {
                $failedids .= "$itemid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = '{_validated_lc}';
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_listing_lower}' : '{_listings_lower}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Listings validated successfully' : 'Failure validating orders'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
    }


    private function endearlylisting($itemid = 0)
    {
        $sql = $this->sheel->db->query("
			SELECT user_id, cid, project_title
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($itemid) . "'
			LIMIT 1
		");
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            $this->sheel->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET status = 'closed',
				close_date = '" . DATETIME24H . "'
				WHERE project_id = '" . intval($itemid) . "'
				LIMIT 1
			");
            if ($res['status'] == 'open') {
                $this->sheel->categories->build_category_count($res['cid'], 'subtract', "admin closing multiple listings from admincp: subtracting increment count category id $res[cid]");
            }
            $this->listings .= $this->counter . ". {_title}: " . o($res['project_title']) . " (#$itemid)" . LINEBREAK;
            $this->counter++;
            return true;
        }
        return 'Could not end early listing id #' . $itemid;
    }

    public function endearly($ids = array())
    {
        $this->listings = $allerrors = $successids = $failedids = '';
        $count = 0;
        $this->counter = 1;
        foreach ($ids as $inc => $itemid) {
            $response = $this->endearlylisting($itemid);
            if ($response === true) {
                $successids .= "$itemid~";
                $count++;
            } else {
                $failedids .= "$itemid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = '{_ended_early_lc}';
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_listing_lower}' : '{_listings_lower}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Listings ended early successfully' : 'Failure ending listings early'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
    }

    private function markstaffpick($itemid = 0)
    {
        $this->sheel->db->query("
			UPDATE " . DB_PREFIX . "projects
			SET isstaffpick = '1'
			WHERE project_id = '" . intval($itemid) . "'
			LIMIT 1
		");
        return true;
    }

    public function mark_staffpick($ids = array())
    {
        $allerrors = $successids = $failedids = '';
        $count = 0;
        $this->counter = 1;
        foreach ($ids as $inc => $itemid) {
            $response = $this->markstaffpick($itemid);
            if ($response === true) {
                $successids .= "$itemid~";
                $count++;
            } else {
                $failedids .= "$itemid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = 'marked as staff pick for';
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_listing_lower}' : '{_listings_lower}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Listings marked as staff picks successfully' : 'Failure marking listings as staff picks'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
    }
    private function unmarkstaffpick($itemid = 0)
    {
        $this->sheel->db->query("
			UPDATE " . DB_PREFIX . "projects
			SET isstaffpick = '0'
			WHERE project_id = '" . intval($itemid) . "'
			LIMIT 1
		");
        return true;
    }
    public function unmark_staffpick($ids = array())
    {
        $allerrors = $successids = $failedids = '';
        $count = 0;
        $this->counter = 1;
        foreach ($ids as $inc => $itemid) {
            $response = $this->unmarkstaffpick($itemid);
            if ($response === true) {
                $successids .= "$itemid~";
                $count++;
            } else {
                $failedids .= "$itemid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = 'unmarked as staff pick for';
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_listing_lower}' : '{_listings_lower}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Listings unmarked as staff picks successfully' : 'Failure unmarking listings as staff picks'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
    }

    private function deletelisting($itemid = 0)
    {
        $sql = $this->sheel->db->query("
			SELECT user_id, cid, project_title
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($itemid) . "'
			LIMIT 1
		");
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            $this->sheel->common_listing->physically_remove_listing(intval($itemid));
            $this->listings .= $this->counter . ". {_title}: " . o($res['project_title']) . " (#$itemid)" . LINEBREAK;
            $this->counter++;
            return true;
        }
        return 'Could not delete listing id #' . $itemid;
    }

    public function delete($ids = array())
    {
        $this->listings = $allerrors = $successids = $failedids = '';
        $count = 0;
        $this->counter = 1;
        foreach ($ids as $inc => $itemid) {
            $response = $this->deletelisting($itemid);
            if ($response === true) {
                $successids .= "$itemid~";
                $count++;
            } else {
                $failedids .= "$itemid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = '{_deleted_lc}';
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_listing_lower}' : '{_listings_lower}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Listings deleted successfully' : 'Failure deleting listings'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
    }

    private function delistlisting($itemid = 0)
    {
        $sql = $this->sheel->db->query("
			SELECT user_id, cid, project_title, status
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($itemid) . "'
			LIMIT 1
		");
        if ($this->sheel->db->num_rows($sql) > 0) {
            $res = $this->sheel->db->fetch_array($sql, DB_ASSOC);
            if ($res['status'] == 'open') {
                $this->sheel->categories->build_category_count($res['cid'], 'subtract', "admin delisting listings from admincp: subtracting increment count category id $res[cid]");
            }
            $this->sheel->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET status = 'delisted',
				close_date = '" . DATETIME24H . "'
				WHERE project_id = '" . intval($itemid) . "'
			");
            $this->listings .= $this->counter . ". {_title}: " . o($res['project_title']) . " (#$itemid)" . LINEBREAK;
            $this->counter++;
            return true;
        }
        return 'Could not delist listing id #' . $itemid;
    }

    public function delist($ids = array())
    {
        $this->listings = $allerrors = $successids = $failedids = '';
        $count = 0;
        $this->counter = 1;
        foreach ($ids as $inc => $itemid) {
            $response = $this->delistlisting($itemid);
            if ($response === true) {
                $successids .= "$itemid~";
                $count++;
            } else {
                $failedids .= "$itemid~";
                $allerrors .= $response . '|';
            }
        }
        $this->sheel->template->templateregistry['action'] = '{_delisted_lower}';
        $this->sheel->template->templateregistry['actionplural'] = (($count == 1) ? '{_listing_lower}' : '{_listings_lower}');
        $success = '{_successfully_x_x_x::' . $this->sheel->template->parse_template_phrases('action') . '::' . $count . '::' . $this->sheel->template->parse_template_phrases('actionplural') . '}';
        $this->sheel->template->templateregistry['success'] = $success;
        $this->sheel->log_event($_SESSION['sheeldata']['user']['userid'], basename(__FILE__), (($count > 0) ? 'success' : 'failure') . "\n" . $this->sheel->array2string($this->sheel->GPC), (($count > 0) ? 'Listings delisted successfully' : 'Failure delisting listings'), (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : $allerrors));
        return array('success' => (($count > 0) ? $this->sheel->template->parse_template_phrases('success') : ''), 'errors' => $allerrors, 'successids' => $successids, 'failedids' => $failedids);
    }
}
?>