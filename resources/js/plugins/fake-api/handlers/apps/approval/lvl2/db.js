import { BASE_API_URL } from '@/plugins/1.router/additional-routes';
const token = useCookie('accessToken').value

export async function fetchApprovalLvl2OnGoing(currentPage, rowPerPage, rowSearch, priorityFilter, expiredFilter) {
  let start = 0;
  const url = `${BASE_API_URL}/api/apps/approval-lvl2-ongoing/search`;

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
        "value": ["4","16"]
      },
      {
        "name": "aud_delete",
        "logic_operator": "ISNULL",
        "value": "NULL"
      },
      {
        "name": "sts_id",
        "logic_operator": "=",
        "value": "4",
        "table_name": "trn_time_history"
      },
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
          "name": "aud_user",
          "logic_operator": "like",
          "value": rowSearch,
          "table_name": "trn_time_history"
        },
        {
          "name": "description",
          "logic_operator": "like",
          "value": rowSearch,
          "table_name": "mer_bu_cc_wc"
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
        "name": "trn_time_history",
        "column_join": "con_req_id",
        "column_self": "con_id",
        "column_results": ["aud_user","aud_date"]
      },
    ],
    "orders": {
      "columns": ["con_req_id"],
      "ascending": false
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
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    throw new Error("Failed to fetching PPS on going data");
  }
}

export async function fetchApprovalLvl2Completed(currentPage, rowPerPage, rowSearch, priorityFilter, expiredFilter) {
  let start = 0;
  const url = `${BASE_API_URL}/api/apps/approval-lvl2-completed/search`;

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
        "value": ["1", "2", "3", "4"],
      },
      {
        "name": "aud_delete",
        "logic_operator": "ISNULL",
        "value": "NULL",
      },
      {
        "name": "sts_id",
        "logic_operator": "=",
        "value": "4",
        "table_name": "trn_time_history"
      },
      {
        "name": "group_dimension",
        "logic_operator": "=",
        "value": "Business Unit",
        "table_name": "mer_bu_cc_wc"
      },
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
          "name": "aud_user",
          "logic_operator": "like",
          "value": rowSearch,
          "table_name": "trn_time_history"
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
        "column_results": ["description","group_dimension"],
        "column_self": "number"
      },
      {
        "name": "trn_time_history",
        "column_join": "con_req_id",
        "column_self": "con_id",
        "column_results": ["aud_user","aud_date"]
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
      "ascending": false
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
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    throw new Error("Failed to fetching PPS completed data");
  }
}

export async function addPPS(request) {
  const url = `${BASE_API_URL}/api/apps/pps/add`;
  const formData = new FormData();

  for (const [key, value] of Object.entries(request)) {
    if (typeof value === 'object' && !(value instanceof File)) {
      formData.append(key, JSON.stringify(value));
    } else {
      formData.append(key, value);
    }
  }

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
      },
      body: formData,
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(`Error: ${response.status}, ${errorData.message || 'Unknown error'}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    throw new Error("Failed to create PPS");
  }
}
