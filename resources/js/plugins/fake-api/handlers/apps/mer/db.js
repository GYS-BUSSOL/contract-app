import { BASE_API_URL } from '@/plugins/1.router/additional-routes';
const token = useCookie('accessToken').value

export async function getListMerUser(accessRole) {
  const url = `${BASE_API_URL}/api/configurations/human-resources/list/${accessRole}`;

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
    throw new Error("Failed to fetching user data");
  }
}

export async function getListMerLaborType() {
  const url = `${BASE_API_URL}/api/configurations/labor-type/list`;

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
    throw new Error("Failed to fetching labor type data");
  }
}

export async function getListMerJobType() {
  const url = `${BASE_API_URL}/api/configurations/job-type/list`;

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
    throw new Error("Failed to fetching job type data");
  }
}

export async function getListMerPaymentTemplate() {
  const url = `${BASE_API_URL}/api/apps/mer-payment-template/list`;

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
    throw new Error("Failed to fetching mer payment template data");
  }
}

export async function getListMerPaymentType() {
  const url = `${BASE_API_URL}/api/apps/mer-payment-type/list`;

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
    throw new Error("Failed to fetching mer payment type data");
  }
}

export async function getListMerMeasurementUnit() {
  const url = `${BASE_API_URL}/api/apps/measurement-unit/list`;

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
    throw new Error("Failed to fetching measurement unit data");
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
    throw new Error("Failed to fetching reject category data");
  }
}

export async function getListMerContractStatus() {
  const url = `${BASE_API_URL}/api/apps/mer-contract-status/list`;

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
    throw new Error("Failed to fetching contract status data");
  }
}

export async function getListMerBU() {
  const url = `${BASE_API_URL}/api/apps/mer-bu/list`;

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
    throw new Error("Failed to fetching business unit data");
  }
}

export async function getListMerCC() {
  const url = `${BASE_API_URL}/api/apps/mer-cc/list`;

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
    throw new Error("Failed to fetching cost center data");
  }
}

export async function getListMerWC() {
  const url = `${BASE_API_URL}/api/apps/mer-wc/list`;

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
    throw new Error("Failed to fetching work center data");
  }
}

export async function getListMerVendor() {
  const url = `${BASE_API_URL}/api/apps/mer-vendor/list`;

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
    throw new Error("Failed to fetching vendor data");
  }
}

export async function getListBU() {
  const url = `${BASE_API_URL}/api/apps/bu/list`;

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
    throw new Error("Failed to fetching business unit data");
  }
}

export async function getListContract() {
  const url = `${BASE_API_URL}/api/apps/contract/list`;

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
    throw new Error("Failed to fetching contract data");
  }
}

export async function getListSignatureType() {
  const url = `${BASE_API_URL}/api/configurations/signature-type/list`;

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
    throw new Error("Failed to fetching signature type data");
  }
}
