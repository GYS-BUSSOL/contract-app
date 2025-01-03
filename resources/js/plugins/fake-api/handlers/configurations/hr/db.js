import { BASE_API_URL } from '@/plugins/1.router/additional-routes';
const token = useCookie('accessToken').value

export async function fetchHumanResources(currentPage, rowPerPage, rowSearch, roleFilter) {
  let start = 0;
  const url = `${BASE_API_URL}/api/configurations/human-resources/search`;
  
  if(currentPage != 1 && currentPage > 1)
    start = (currentPage * rowPerPage) - rowPerPage

  const payload = {
    "paging": {
        "start": start,
        "length": rowPerPage
    },
    "columns": [
      {
        "name": "is_active",
        "logic_operator": "=",
        "value": 0
      },
    ],
    "group_column": {
      "operator": "AND",
      "group_operator": "OR",
      "where": [
        {
          "name": "usr_name",
          "logic_operator": "like",
          "value": rowSearch
        },
        {
          "name": "usr_display_name",
          "logic_operator": "like",
          "value": rowSearch
        },
      ]
    },
    "joins": [],
    "orders": {
      "columns": ["usr_id"],
      "ascending": false
    }
  };

  if(roleFilter) {
    payload['columns'].push(
      {
        "name": "usr_access",
        "logic_operator": "=",
        "value": roleFilter,
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
    throw new Error("Failed to fetching human resources data");
  }
}
