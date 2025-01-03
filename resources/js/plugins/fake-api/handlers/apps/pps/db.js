import { BASE_API_URL } from '@/plugins/1.router/additional-routes';

export async function fetchPPSOnGoing(currentPage, rowPerPage, rowSearch, priorityFilter, statusFilter, expiredFilter) {
  const token = useCookie('accessToken').value
  let start = 0;
  const url = `${BASE_API_URL}/api/apps/pps-ongoing/search`;

  if(currentPage != 1 && currentPage > 1)
    start = (currentPage * rowPerPage) - rowPerPage

  const payload = {
    "paging": {
        "start": start,
        "length": rowPerPage
    },
    "columns": [
      {
        "name": "sts_id",
        "logic_operator": "NOTIN",
        "value": ['12','14','15']
      },
      {
        "name": "aud_delete",
        "logic_operator": "ISNULL",
        "value": "NULL"
      }
    ],
    "group_column": {
      "operator": "AND",
      "group_operator": "OR",
      "where": [
        {
          "name": "con_req_no",
          "logic_operator": "like",
          "value": rowSearch == "1" || rowSearch == "2" ? '' : rowSearch
        },
        {
          "name": "con_bu",
          "logic_operator": "like",
          "value": rowSearch == "1" || rowSearch == "2" ? '' : rowSearch
        },
        {
          "name": "con_pps_no",
          "logic_operator": "like",
          "value": rowSearch == "1" || rowSearch == "2" ? '' : rowSearch
        },
        {
          "name": "aud_user",
          "logic_operator": "like",
          "value": rowSearch == "1" || rowSearch == "2" ? '' : rowSearch
        },
        {
          "name": "description",
          "logic_operator": "like",
          "value": rowSearch == "1" || rowSearch == "2" ? '' : rowSearch,
          "table_name": "mer_bu_cc_wc"
        },
        {
          "name": "spk_no",
          "logic_operator": "like",
          "value": rowSearch == "1" || rowSearch == "2" ? '' : rowSearch,
          "table_name": "trn_spk"
        },
      ]
    },
    "joins": [
      {
        "name": "mer_contract_status",
        "column_join": "sts_id",
        "column_results": ["sts_description"],
        "column_self": "sts_id"
      },
      {
        "name": "mer_bu_cc_wc",
        "column_join": "con_bu",
        "column_results": ["description"],
        "column_self": "number"
      },
      {
        "name": "trn_spk_contract",
        "column_join": "con_req_id",
        "column_self": "con_req_id"
      },
      {
        "name1": "trn_spk_contract",
        "name": "trn_spk",
        "column_join": "spk_id",
        "column_self": "spk_id",
        "column_results": ["spk_no","spk_renewal_box"]
      },
    ],
    "orders": {
      "columns": ["con_req_id"],
      "ascending": true
    }
  };

  if(expiredFilter) {
    payload['columns'].push(
      {
        "name": "con_is_expired",
        "logic_operator": expiredFilter == "null" ? "ISNULL" : "=",
        "value": String(expiredFilter)
      }
    )
  }

  if(priorityFilter) {
    payload['columns'].push(
      {
        "name": "con_priority_id",
        "logic_operator": "like",
        "value": priorityFilter
      }
    )
  }
  
  if(statusFilter) {
    payload['columns'].push(
      {
        "name": "sts_description",
        "logic_operator": "like",
        "value": statusFilter,
        "table_name": "mer_contract_status"
      }
    )
  }

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      throw new Error("Error fetching PPS on going data");
    }

    const data = await response.json();
    return data;

  } catch (error) {
    throw new Error("Error fetching PPS on going data");
  }
}

export async function fetchPPSCompleted(currentPage, rowPerPage, rowSearch, priorityFilter, statusFilter, expiredFilter) {
  const token = useCookie('accessToken').value
  let start = 0;
  const url = `${BASE_API_URL}/api/apps/pps-completed/search`;

  if(currentPage != 1 && currentPage > 1)
    start = (currentPage * rowPerPage) - rowPerPage

  const payload = {
    "paging": {
        "start": start,
        "length": rowPerPage
    },
    "columns": [
      {
        "name": "sts_id",
        "logic_operator": "IN",
        "value": ['5','7','12','14','15'],
      },
      {
        "name": "aud_delete",
        "logic_operator": "ISNULL",
        "value": "NULL",
      }
    ],
    "group_column": {
      "operator": "AND",
      "group_operator": "OR",
      "where": [
        {
          "name": "con_req_no",
          "logic_operator": "like",
          "value": rowSearch
        },
        {
          "name": "con_bu",
          "logic_operator": "like",
          "value": rowSearch
        },
        {
          "name": "con_pps_no",
          "logic_operator": "like",
          "value": rowSearch
        },
        {
          "name": "aud_user",
          "logic_operator": "like",
          "value": rowSearch
        },
        {
          "name": "description",
          "logic_operator": "like",
          "value": rowSearch,
          "table_name": "mer_bu_cc_wc"
        },
        {
          "name": "spk_no",
          "logic_operator": "like",
          "value": rowSearch,
          "table_name": "trn_spk"
        },
      ]
    },
    "joins": [
      {
        "name": "mer_contract_status",
        "column_join": "sts_id",
        "column_results": ["sts_description"],
        "column_self": "sts_id"
      },
      {
        "name": "mer_bu_cc_wc",
        "column_join": "con_bu",
        "column_results": ["description"],
        "column_self": "number"
      },
      {
        "name": "trn_spk_contract",
        "column_join": "con_req_id",
        "column_self": "con_req_id"
      },
      {
        "name1": "trn_spk_contract",
        "name": "trn_spk",
        "column_join": "spk_id",
        "column_self": "spk_id",
        "column_results": ["spk_no","spk_renewal_box"]
      },
    ],
    "orders": {
      "columns": ["con_req_id"],
      "ascending": true
    }
  };

  if(expiredFilter) {
    payload['columns'].push(
      {
        "name": "con_is_expired",
        "logic_operator": expiredFilter == "null" ? "ISNULL" : "=",
        "value": String(expiredFilter)
      }
    )
  }

  if(priorityFilter) {
    payload['columns'].push(
      {
        "name": "con_priority_id",
        "logic_operator": "like",
        "value": priorityFilter
      }
    )
  }
  
  if(statusFilter) {
    payload['columns'].push(
      {
        "name": "sts_description",
        "logic_operator": "like",
        "value": statusFilter,
        "table_name": "mer_contract_status"
      }
    )
  }

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      throw new Error("Error fetching PPS completed data");
    }

    const data = await response.json();
    return data;

  } catch (error) {
    throw new Error("Error fetching PPS completed data");
  }
}
