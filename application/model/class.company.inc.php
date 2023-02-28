<?php

class company {
    protected $sheel;

    function __construct($sheel)
    {
            $this->sheel = $sheel;
    }

    function construct_new_ref()
    {
        $ref = rand(1, 9) . mb_substr(time(), -7, 10);
        $sql = $this->sheel->db->query("
			SELECT company_ref
			FROM " . DB_PREFIX . "companies
			WHERE company_ref = '" . intval($ref) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
        if ($this->sheel->db->num_rows($sql) > 0)
        {
            $ref = rand(1, 9) . mb_substr(time(), -6, 10);
            $sql = $this->sheel->db->query("
				SELECT company_ref
				FROM " . DB_PREFIX . "companies
				WHERE company_ref = '" . intval($ref) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
            if ($this->sheel->db->num_rows($sql) > 0)
            {
                $ref = rand(1, 9) . mb_substr(time(), -8, 10);
                $sql = $this->sheel->db->query("
					SELECT company_ref
					FROM " . DB_PREFIX . "companies
					WHERE company_ref = '" . intval($ref) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
                if ($this->sheel->db->num_rows($sql) > 0)
                {
                    $ref = rand(1, 9) . mb_substr(time(), -8, 10);
                    return $ref;
                }
                else
                {
                    return $ref;
                }
            }
            else
            {
                return $ref;
            }
        }
        else
        {
            return $ref;
        }
    }
}

?>