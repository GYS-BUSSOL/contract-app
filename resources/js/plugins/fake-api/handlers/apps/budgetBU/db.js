import { BASE_API_URL } from '@/plugins/1.router/additional-routes';

export async function fetchBudgetBU(currentPage, rowPerPage, rowSearch, yearFilter) {
  let start = 0;
  const currentYear = new Date().getFullYear();
  const year = yearFilter || currentYear;
  const url = `${BASE_API_URL}/api/apps/budget-bu/search`;
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
        "name": "group_dimension",
        "logic_operator": "=",
        "value": "Business Unit"
      },
    ],
    "group_column": {
      "operator": "AND",
      "group_operator": "OR",
      "where": [
        {
          "name": "description",
          "logic_operator": "like",
          "value": rowSearch,
        },
        {
          "name": "number",
          "logic_operator": "like",
          "value": rowSearch,
        },
      ]
    },
    "joins": [
      {
        "name": "trn_budget",
        "column_join": "number",
        "column_self": "bgt_bu",
        "column_results": ["bgt_year","bgt_bu_head", "bgt_amount", "bgt_expense", "bgt_balance"]
      }
    ],
    "orders": {
      "columns": ["id"],
      "ascending": false
    },
    "active_year" : year
  };

  if(yearFilter) {
    payload['columns'].push(
      {
        "name": "bgt_year",
        "logic_operator": "=",
        "value": yearFilter,
        "table_name": "trn_budget"
      },
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
    console.error("Error fetching budget bu data:", error);
    throw error;
  }
}

