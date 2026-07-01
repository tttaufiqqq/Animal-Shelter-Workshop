import { useState } from 'react'
import { X, MapPin, Eye, CheckCircle, Clock, AlertCircle } from 'lucide-react'

interface ReportImage { image_path: string; url?: string }
interface Report {
  id: number
  address: string
  city: string
  state: string
  report_status: string
  description: string
  created_at: string
  latitude?: string | number
  longitude?: string | number
  images?: ReportImage[]
}

interface PaginatedReports {
  data: Report[]
  meta?: { total: number }
}

interface Props {
  reports: PaginatedReports
  onClose: () => void
}

const STATUS_COLORS: Record<string, string> = {
  Pending: 'bg-yellow-100 text-yellow-800',
  Assigned: 'bg-blue-100 text-blue-800',
  'In Progress': 'bg-purple-100 text-purple-800',
  Completed: 'bg-green-100 text-green-800',
  Rejected: 'bg-red-100 text-red-800',
}

const STATUS_STEPS = ['Report Submitted', 'Caretaker Assigned', 'Rescue In Progress', 'Rescue Completed']
const STATUS_TO_STEP: Record<string, number> = {
  Pending: 0, Assigned: 1, 'In Progress': 2, Completed: 3
}

function cloudUrl(path: string) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  const cloud = (import.meta as any).env?.VITE_CLOUDINARY_CLOUD_NAME
  return cloud ? `https://res.cloudinary.com/${cloud}/image/upload/${path}` : path
}

function ReportDetailModal({ report, onClose }: { report: Report; onClose: () => void }) {
  const currentStep = STATUS_TO_STEP[report.report_status] ?? 0
  const lat = report.latitude ? String(report.latitude) : null
  const lng = report.longitude ? String(report.longitude) : null

  return (
    <div className="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/70">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col">
        {/* Header */}
        <div className="bg-gradient-to-r from-purple-600 to-indigo-700 text-white px-6 py-4 flex items-start justify-between">
          <div>
            <div className="flex items-center gap-3 mb-1">
              <div className="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <MapPin size={16} />
              </div>
              <h3 className="text-lg font-bold">Report #{report.id}</h3>
            </div>
            <p className="text-purple-200 text-xs">Complete information about this report</p>
          </div>
          <button onClick={onClose} className="text-white/70 hover:text-white mt-1">
            <X size={18} />
          </button>
        </div>

        <div className="overflow-y-auto flex-1 p-5 space-y-4">
          {/* Status + Date */}
          <div className="flex items-center justify-between">
            <span className={`px-3 py-1 rounded-full text-xs font-semibold ${STATUS_COLORS[report.report_status] ?? 'bg-gray-100 text-gray-700'}`}>
              {report.report_status}
            </span>
            <span className="text-xs text-gray-500">
              {new Date(report.created_at).toLocaleString('en-MY', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
            </span>
          </div>

          {/* Progress Stepper */}
          <div className="flex items-center gap-0">
            {STATUS_STEPS.map((step, i) => {
              const done = i < currentStep
              const active = i === currentStep
              const isLast = i === STATUS_STEPS.length - 1
              return (
                <div key={step} className="flex items-center flex-1">
                  <div className="flex flex-col items-center flex-shrink-0">
                    <div className={`w-9 h-9 rounded-full flex items-center justify-center border-2 transition-colors ${done ? 'bg-purple-600 border-purple-600' : active ? 'bg-purple-600 border-purple-600' : 'bg-gray-100 border-gray-300'}`}>
                      {done ? <CheckCircle size={18} className="text-white" /> : active ? <Clock size={18} className="text-white" /> : <div className="w-2 h-2 rounded-full bg-gray-400" />}
                    </div>
                    <p className={`text-center text-[10px] mt-1 font-medium max-w-[60px] leading-tight ${active ? 'text-purple-700' : done ? 'text-gray-700' : 'text-gray-400'}`}>{step}</p>
                    {active && <p className="text-[9px] text-purple-500 font-medium">Current Status</p>}
                  </div>
                  {!isLast && (
                    <div className={`flex-1 h-0.5 mx-1 mb-5 ${i < currentStep ? 'bg-purple-600' : 'bg-gray-200'}`} />
                  )}
                </div>
              )
            })}
          </div>

          {/* Location */}
          <div className="bg-purple-50 rounded-xl p-4 space-y-2">
            <div className="flex items-center gap-2 mb-2">
              <MapPin size={15} className="text-purple-600" />
              <span className="font-semibold text-gray-800 text-sm">Location Details</span>
            </div>
            <div>
              <p className="text-xs text-gray-500">Address</p>
              <p className="text-sm font-medium text-gray-800">{report.address}</p>
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div>
                <p className="text-xs text-gray-500">City</p>
                <p className="text-sm font-medium text-gray-800">{report.city}</p>
              </div>
              <div>
                <p className="text-xs text-gray-500">State</p>
                <p className="text-sm font-medium text-gray-800">{report.state}</p>
              </div>
            </div>
          </div>

          {/* Map */}
          {lat && lng && (
            <div className="space-y-2">
              <div className="flex items-center gap-2">
                <MapPin size={15} className="text-purple-600" />
                <span className="font-semibold text-gray-800 text-sm">Map Location</span>
              </div>
              <div className="rounded-xl overflow-hidden border border-gray-200 h-44">
                <iframe
                  title="Report location"
                  src={`https://www.openstreetmap.org/export/embed.html?bbox=${parseFloat(lng)-0.01},${parseFloat(lat)-0.01},${parseFloat(lng)+0.01},${parseFloat(lat)+0.01}&layer=mapnik&marker=${lat},${lng}`}
                  className="w-full h-full border-0"
                  loading="lazy"
                />
              </div>
            </div>
          )}

          {/* Description */}
          <div className="space-y-1">
            <div className="flex items-center gap-2">
              <AlertCircle size={15} className="text-purple-600" />
              <span className="font-semibold text-gray-800 text-sm">Description</span>
            </div>
            <p className="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2">{report.description}</p>
          </div>

          {/* Images */}
          {report.images && report.images.length > 0 && (
            <div className="space-y-2">
              <div className="flex items-center gap-2">
                <Eye size={15} className="text-purple-600" />
                <span className="font-semibold text-gray-800 text-sm">Images ({report.images.length})</span>
              </div>
              <div className="grid grid-cols-3 gap-2">
                {report.images.map((img, i) => (
                  <img
                    key={i}
                    src={img.url ?? cloudUrl(img.image_path)}
                    alt={`Report image ${i + 1}`}
                    className="h-24 w-full object-cover rounded-lg"
                  />
                ))}
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

export default function MyReportsModal({ reports, onClose }: Props) {
  const [selectedReport, setSelectedReport] = useState<Report | null>(null)

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">
        {/* Header */}
        <div className="bg-gradient-to-r from-purple-600 to-indigo-700 text-white px-6 py-5 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
              <MapPin size={20} />
            </div>
            <div>
              <h2 className="text-xl font-bold">My Reports</h2>
              <p className="text-purple-200 text-sm">View all your submitted reports (Live status updates)</p>
            </div>
          </div>
          <button onClick={onClose} className="text-white/70 hover:text-white transition-colors">
            <X size={20} />
          </button>
        </div>

        {/* Table */}
        <div className="overflow-auto flex-1">
          {reports.data.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-16 text-gray-500">
              <MapPin size={40} className="text-gray-300 mb-3" />
              <p className="font-medium">No reports submitted yet.</p>
              <p className="text-sm text-gray-400 mt-1">Submit your first stray animal report.</p>
            </div>
          ) : (
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-purple-600 text-white text-xs font-semibold uppercase tracking-wide">
                  <th className="px-4 py-3 text-left">Report ID</th>
                  <th className="px-4 py-3 text-left">Status</th>
                  <th className="px-4 py-3 text-left">Date & Time</th>
                  <th className="px-4 py-3 text-left">Location</th>
                  <th className="px-4 py-3 text-left">Description</th>
                  <th className="px-4 py-3 text-left">Images</th>
                  <th className="px-4 py-3 text-left">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {reports.data.map(r => (
                  <tr key={r.id} className="hover:bg-gray-50 transition-colors">
                    <td className="px-4 py-3">
                      <div className="flex items-center gap-1.5 font-mono font-semibold text-purple-700">
                        <MapPin size={13} className="text-red-500" />
                        #{r.id}
                      </div>
                    </td>
                    <td className="px-4 py-3">
                      <span className={`inline-flex px-2.5 py-1 rounded-full text-xs font-semibold ${STATUS_COLORS[r.report_status] ?? 'bg-gray-100 text-gray-800'}`}>
                        {r.report_status}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">
                      {new Date(r.created_at).toLocaleString('en-MY', { day: '2-digit', month: 'short', year: 'numeric' })}
                      <br />
                      {new Date(r.created_at).toLocaleTimeString('en-MY', { hour: '2-digit', minute: '2-digit' })}
                    </td>
                    <td className="px-4 py-3 max-w-[180px]">
                      <p className="font-medium text-gray-800 text-xs truncate">{r.address}</p>
                      <p className="text-gray-500 text-xs">{r.city}, {r.state}</p>
                    </td>
                    <td className="px-4 py-3 max-w-[180px]">
                      <p className="text-xs text-gray-600 truncate">{r.description}</p>
                    </td>
                    <td className="px-4 py-3">
                      {r.images && r.images.length > 0 ? (
                        <div className="flex items-center gap-1">
                          {r.images.slice(0, 3).map((img, i) => (
                            <img
                              key={i}
                              src={img.url ?? cloudUrl(img.image_path)}
                              alt=""
                              className="w-7 h-7 rounded-full object-cover border-2 border-white -ml-1 first:ml-0"
                            />
                          ))}
                          {r.images.length > 3 && (
                            <span className="text-xs text-gray-500 ml-1">+{r.images.length - 3}</span>
                          )}
                        </div>
                      ) : (
                        <span className="text-xs text-gray-400">None</span>
                      )}
                    </td>
                    <td className="px-4 py-3">
                      <button
                        onClick={() => setSelectedReport(r)}
                        className="flex items-center gap-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition-colors"
                      >
                        <Eye size={12} />
                        View
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </div>

      {selectedReport && (
        <ReportDetailModal report={selectedReport} onClose={() => setSelectedReport(null)} />
      )}
    </div>
  )
}
