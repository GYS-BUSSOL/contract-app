import { fetchSignatureType } from '@db/configurations/signature-type/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerConfigurationsSignatureType = [
    // Get Signature Type Details
    http.get(('/api/configurations/signature-type/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const sortBy = url.searchParams.get('sortBy')
      const itemsPerPage = url.searchParams.get('itemsPerPage')
      const page = url.searchParams.get('page')
      const orderBy = url.searchParams.get('orderBy')
      const searchQuery = is.string(q) ? q : undefined
      let queryLower = (searchQuery ?? '').toString().toLowerCase()
      const parsedSortBy = destr(sortBy)
      const sortByLocal = is.string(parsedSortBy) ? parsedSortBy : ''
      const parsedOrderBy = destr(orderBy)
      const orderByLocal = is.string(parsedOrderBy) ? parsedOrderBy : ''
      const parsedItemsPerPage = destr(itemsPerPage)
      const itemsPerPageLocal = is.number(parsedItemsPerPage) ? parsedItemsPerPage : 10
      // Fetch Data
      const fetchData = await fetchSignatureType(page, itemsPerPage, queryLower);
      const dataRows = fetchData.data.rows;
      // Filter Signature Type
      let filteredSignatureType = dataRows.filter(signatureType => (
        (signatureType.st_desc?.toLowerCase().includes(queryLower))
      )).reverse()

      // Sort Signature Type
      if (sortByLocal) {
        if (sortByLocal === 'st_desc') {
          filteredSignatureType = filteredSignatureType.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.st_desc.localeCompare(b.st_desc)
            else
              return b.st_desc.localeCompare(a.st_desc)
          })
        }
      }
      const totalSignatureType = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalSignatureType / itemsPerPageLocal)
      
      return HttpResponse.json({
        signatureType: filteredSignatureType,
        totalPages,
        totalSignatureType,
        page,
      }, { status: 200 })
    }),
]
