import { BASE_API_URL } from '@/plugins/1.router/additional-routes';

export async function fetchVendorAssignment(currentPage, rowPerPage, rowSearch, priorityFilter, statusFilter, expiredFilter) {
  let start = 0;
  const url = `${BASE_API_URL}/api/apps/vendor-assigment/search`;
  const token = 'YOUR_BEARER_TOKEN_HERE';

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
        "logic_operator": "=",
        "value": "1",
        "operator": "AND"
      },
      {
        "name": "aud_delete",
        "logic_operator": "ISNULL",
        "value": "NULL",
        "operator": "AND"
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
      }
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
        // 'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;

  } catch (error) {
    console.error("Error fetching vendor assignment data:", error);
    throw error;
  }
}

