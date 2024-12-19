import { BASE_API_URL } from '@/plugins/1.router/additional-routes';
const token = useCookie('accessToken').value

export async function getListMerUser(accessRole) {
  const url = `${BASE_API_URL}/api/configurations/human-resources/list/${accessRole}`;
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
    console.error(`Error fetching user ${accessRole} data`);
    throw error;
  }
}

export async function getListMerLaborType() {
  const url = `${BASE_API_URL}/api/configurations/labor-type/list`;
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
    console.error("Error fetching labor type data");
    throw error;
  }
}

export async function getListMerJobType() {
  const url = `${BASE_API_URL}/api/configurations/job-type/list`;
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
    console.error("Error fetching job type data");
    throw error;
  }
}

export async function getListMerPaymentTemplate() {
  const url = `${BASE_API_URL}/api/apps/mer-payment-template/list`;
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
    console.error("Error fetching payment template data");
    throw error;
  }
}

export async function getListMerPaymentType() {
  const url = `${BASE_API_URL}/api/apps/mer-payment-type/list`;
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
    console.error("Error fetching payment type data");
    throw error;
  }
}

export async function getListMerMeasurementUnit() {
  const url = `${BASE_API_URL}/api/apps/measurement-unit/list`;
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
    console.error("Error fetching measurement unit data");
    throw error;
  }
}

export async function getListRejectCategory() {
  const url = `${BASE_API_URL}/api/apps/reject-category/list`;

  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(`Error: ${response.status}, ${errorData.message || 'Unknown error'}`);
    }

    const data = await response.json();
    return data;

  } catch (error) {
    console.error("Error fetching reject category data");
    throw error;
  }
}

export async function getListMerContractStatus() {
  const url = `${BASE_API_URL}/api/apps/mer-contract-status/list`;
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
    console.error("Error fetching contract status data");
    throw error;
  }
}

export async function getListMerBU() {
  const url = `${BASE_API_URL}/api/apps/mer-bu/list`;
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
    console.error("Error fetching business unit data");
    throw error;
  }
}

export async function getListMerCC() {
  const url = `${BASE_API_URL}/api/apps/mer-cc/list`;
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
    console.error("Error fetching cost center data");
    throw error;
  }
}

export async function getListMerWC() {
  const url = `${BASE_API_URL}/api/apps/mer-wc/list`;
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
    console.error("Error fetching work center data");
    throw error;
  }
}

export async function getListMerVendor() {
  const url = `${BASE_API_URL}/api/apps/mer-vendor/list`;
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
    console.error("Error fetching vendor data");
    throw error;
  }
}

export async function getListBU() {
  const url = `${BASE_API_URL}/api/apps/bu/list`;
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
    console.error("Error fetching business units data");
    throw error;
  }
}

export async function getListContract() {
  const url = `${BASE_API_URL}/api/apps/contract/list`;
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
    console.error("Error fetching contract data");
    throw error;
  }
}

export async function getListSignatureType() {
  const url = `${BASE_API_URL}/api/configurations/signature-type/list`;
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
    console.error("Error fetching signature type data");
    throw error;
  }
}
