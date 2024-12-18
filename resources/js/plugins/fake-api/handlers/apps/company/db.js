import { BASE_API_URL } from '@/plugins/1.router/additional-routes';

export async function getListCompany() {
  const url = `${BASE_API_URL}/api/apps/company/list`;
  // const token = localStorage.getItem('token') || 'YOUR_BEARER_TOKEN_HERE';

  try {
    const response = await fetch(url, {
      method: 'GET'
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(`Error: ${response.status}, ${errorData.message || 'Unknown error'}`);
    }

    const data = await response.json();
    return data;

  } catch (error) {
    console.error("Error fetching company data:", error);
    throw error;
  }
}
