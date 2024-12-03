import { setupWorker } from 'msw/browser'

// Handlers
import { handlerAppBarSearch } from '@db/app-bar-search/index'
import { handlerAppsAcademy } from '@db/apps/academy/index'
import { handlerAppsApproval1 } from '@db/apps/approval/lvl1/index'
import { handlerAppsApproval2 } from '@db/apps/approval/lvl2/index'
import { handlerAppsBudgetBU } from '@db/apps/budgetBU/index'
import { handlerAppsCalendar } from '@db/apps/calendar/index'
import { handlerAppsChat } from '@db/apps/chat/index'
import { handlerAppsEcommerce } from '@db/apps/ecommerce/index'
import { handlerAppsEmail } from '@db/apps/email/index'
import { handlerAppsInvoice } from '@db/apps/invoice/index'
import { handlerAppsKanban } from '@db/apps/kanban/index'
import { handlerAppLogistics } from '@db/apps/logistics/index'
import { handlerAppsPBL } from '@db/apps/pbl/index'
import { handlerAppsPermission } from '@db/apps/permission/index'
import { handlerAppsPPS } from '@db/apps/pps/index'
import { handlerAppsRenewal } from '@db/apps/renewal/index'
import { handlerAppsReviewer } from '@db/apps/reviewer/index'
import { handlerAppsSPKActive } from '@db/apps/spk/active/index'
import { handlerAppsSPKList } from '@db/apps/spk/list/index'
import { handlerAppsSPKReport } from '@db/apps/spk/report/index'
import { handlerAppsUsers } from '@db/apps/users/index'
import { handlerAppsVendorAssigment } from '@db/apps/vendorAssigment/index'
import { handlerAuth } from '@db/auth/index'
import { handlerConfigurationsAreaBU } from '@db/configurations/area-management/business-unit/index'
import { handlerConfigurationsAreaCC } from '@db/configurations/area-management/cost-center/index'
import { handlerConfigurationsAreaWC } from '@db/configurations/area-management/work-center/index'
import { handlerConfigurationsHR } from '@db/configurations/hr/index'
import { handlerConfigurationsJobType } from '@db/configurations/job-type/index'
import { handlerConfigurationsManDays } from '@db/configurations/manDays-rate/index'
import { handlerConfigurationsSignatureType } from '@db/configurations/signature-type/index'
import { handlerDashboard } from '@db/dashboard/index'
import { handlerPagesDatatable } from '@db/pages/datatable/index'
import { handlerPagesFaq } from '@db/pages/faq/index'
import { handlerPagesHelpCenter } from '@db/pages/help-center/index'
import { handlerPagesProfile } from '@db/pages/profile/index'

const worker = setupWorker(...handlerAppsEcommerce, ...handlerAppsAcademy, ...handlerAppsInvoice, ...handlerAppsUsers, ...handlerConfigurationsManDays, ...handlerConfigurationsHR, ...handlerConfigurationsAreaBU, ...handlerConfigurationsAreaCC, ...handlerConfigurationsAreaWC, ...handlerConfigurationsJobType, ...handlerConfigurationsSignatureType, ...handlerAppsVendorAssigment, ...handlerAppsBudgetBU, ...handlerAppsReviewer, ...handlerAppsPBL, ...handlerAppsSPKList, ...handlerAppsSPKReport, ...handlerAppsSPKActive, ...handlerAppsRenewal, ...handlerAppsPPS, ...handlerAppsApproval1, ...handlerAppsApproval2, ...handlerAppsEmail, ...handlerAppsCalendar, ...handlerAppsChat, ...handlerAppsPermission, ...handlerPagesHelpCenter, ...handlerPagesProfile, ...handlerPagesFaq, ...handlerPagesDatatable, ...handlerAppBarSearch, ...handlerAppLogistics, ...handlerAuth, ...handlerAppsKanban, ...handlerDashboard)
export default function () {
  const workerUrl = `${import.meta.env.BASE_URL.replace(/build\/$/g, '') ?? '/'}mockServiceWorker.js`
  
  worker.start({
    serviceWorker: {
      url: workerUrl,
    },
    onUnhandledRequest: 'bypass',
  })
}
