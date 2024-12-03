export async function fetchSPKList(currentPage, rowPerPage, rowSearch, expiredFilter) {
  let start = 0;
  const url = 'http://localhost:8000/api/apps/spk-list/search';
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
        "logic_operator": "IN",
        "value": ['6', '10', '8', '17', '18', '19', '9'],
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
          "name": "spk_no",
          "logic_operator": "like",
          "value": rowSearch,
          "table_name": "trn_spk"
        },
        {
          "name": "spk_renewal_box",
          "logic_operator": "like",
          "value": rowSearch,
          "table_name": "trn_spk"
        },
        {
          "name": "spk_jobdesc_summary",
          "logic_operator": "like",
          "value": rowSearch,
          "table_name": "trn_spk"
        },
        {
          "name": "vnd_name",
          "logic_operator": "like",
          "value": rowSearch,
          "table_name": "mer_vendor"
        },
        {
          "name": "vnd_contact_person",
          "logic_operator": "like",
          "value": rowSearch,
          "table_name": "mer_vendor"
        },
      ]
    },
    "joins": [
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
        "column_results": ["spk_id","spk_no","spk_renewal_box","spk_jobdesc_summary","spk_transaction_status","spk_date","spk_start_date","spk_end_date"]
      },
      {
        "name1": "trn_spk",
        "name": "mer_vendor",
        "column_join": "ven_id",
        "column_results": ["vnd_name","vnd_contact_person"],
        "column_self": "vnd_id"
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
    console.error("Error fetching SPK list data:", error);
    throw error;
  }
}

