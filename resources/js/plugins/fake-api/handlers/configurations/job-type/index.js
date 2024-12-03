import { fetchJobType } from '@db/configurations/job-type/db'
import is from '@sindresorhus/is'
import destr from 'destr'
import { HttpResponse, http } from 'msw'

export const handlerConfigurationsJobType = [
    // Get Job Type Details
    http.get(('/api/configurations/job-type/search'), async ({ request }) => {
      const url = new URL(request.url)
      const q = url.searchParams.get('q')
      const status = url.searchParams.get('status')
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
      const fetchData = await fetchJobType(page, itemsPerPage, queryLower, status);
      const dataRows = fetchData.data.rows;
      const totalActiveCount = fetchData.data.total_active || 0
      const totalNotActiveCount = fetchData.data.total_not_active || 0
      // Filter Job Type
      let filteredJobType = dataRows.filter(jobType => (
        (jobType.job_type?.toLowerCase().includes(queryLower))
      )).reverse()

      // Sort Job Type
      if (sortByLocal) {
        if (sortByLocal === 'job_type') {
          filteredJobType = filteredJobType.sort((a, b) => {
            if (orderByLocal === 'asc')
              return a.job_type.localeCompare(b.job_type)
            else
              return b.job_type.localeCompare(a.job_type)
          })
        }
      }
      const totalJobType = fetchData.data.total_record
      // Total pages
      const totalPages = Math.ceil(totalJobType / itemsPerPageLocal)
      
      return HttpResponse.json({
        jobType: filteredJobType,
        totalPages,
        totalJobType,
        totalActiveCount,
        totalNotActiveCount,
        page,
      }, { status: 200 })
    }),
]
