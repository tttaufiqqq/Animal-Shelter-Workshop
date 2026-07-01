import { useState } from 'react'
import { X, BookOpen, User, Shield, Heart, FileText, List, CheckCircle } from 'lucide-react'
import { usePage } from '@inertiajs/react'
import type { PageProps } from '@/types'

interface Props { onClose: () => void }

const TABS = ['Public User', 'Adoption Process'] as const

const testAccounts = [
  { role: 'Admin', icon: Shield, color: 'text-red-600', emails: ['admin1@gmail.com', 'admin2@gmail.com'] },
  { role: 'Caretaker', icon: Heart, color: 'text-green-600', emails: ['caretaker1@gmail.com', 'caretaker2@gmail.com'] },
  { role: 'Public User', icon: User, color: 'text-blue-600', emails: ['taufiq@gmail.com', 'shafiqah@gmail.com'] },
]

const publicUserFeatures = [
  { icon: FileText, title: 'Submit Stray Animal Reports', description: 'Report stray animals you encounter by clicking', link: 'Submit Stray Animal Report', linkText: 'Submit Stray Animal Report', end: ' button. Include location, photos, and description.' },
  { icon: Heart, title: 'Adopt Animals', description: 'Browse available animals and book adoption appointments through ', link: 'booking:main', linkText: 'Booking & Adoption', end: ' menu.' },
  { icon: List, title: 'Track Your Reports', description: 'View status of your submitted reports by clicking ', link: null, linkText: 'My Submitted Reports', end: ' button.' },
]

const adoptionSteps = [
  { step: 1, title: 'Browse Animals', description: 'Navigate to ', link: 'animal-management.index', linkText: 'Animal', end: ' menu to browse all animals available for adoption. Filter by species, age, or health status.' },
  { step: 2, title: 'View Animal Details', description: 'Click on any animal to view comprehensive details including photos, personality traits, medical history, vaccination records, and special care requirements.' },
  { step: 3, title: 'Add Animals to Visit List', description: 'If you\'re interested in an animal, click "Add to Visit List". You can add multiple animals to your list for consideration.' },
  { step: 4, title: 'Schedule a Visit', description: 'From your Visit List, select your preferred date and time. Our staff will confirm your appointment.' },
  { step: 5, title: 'Complete Adoption', description: 'At your appointment, complete the adoption process and pay the adoption fee through our secure payment gateway.' },
]

export default function UserGuideModal({ onClose }: Props) {
  const { auth } = usePage<PageProps>().props
  const [tab, setTab] = useState<typeof TABS[number]>('Public User')
  const roles = auth.user?.roles ?? []

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
        {/* Header */}
        <div className="bg-gradient-to-r from-purple-600 to-indigo-700 text-white px-6 py-5">
          <div className="flex items-start justify-between">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                <BookOpen size={20} />
              </div>
              <div>
                <h2 className="text-xl font-bold">User Guide</h2>
                <p className="text-purple-200 text-sm">Learn how to use the Animal Shelter System</p>
              </div>
            </div>
            <button onClick={onClose} className="text-white/70 hover:text-white transition-colors mt-1">
              <X size={20} />
            </button>
          </div>
          {roles.length > 0 && (
            <div className="mt-3 flex items-center gap-2">
              <span className="text-purple-200 text-xs">Your Role(s):</span>
              {roles.map(r => (
                <span key={r} className="bg-blue-500 text-white text-xs px-2.5 py-1 rounded-full font-medium capitalize">
                  {r === 'public user' ? 'Public User' : r.charAt(0).toUpperCase() + r.slice(1)}
                </span>
              ))}
            </div>
          )}
        </div>

        <div className="overflow-y-auto flex-1 p-6 space-y-5">
          {/* Test Accounts */}
          <div className="border border-yellow-200 bg-yellow-50 rounded-xl p-4">
            <div className="flex items-center justify-between mb-3">
              <div className="flex items-center gap-2">
                <span className="text-lg">🔑</span>
                <span className="font-semibold text-gray-800 text-sm">Test Accounts</span>
              </div>
              <span className="text-orange-600 text-xs font-medium">Password: password</span>
            </div>
            <div className="grid grid-cols-3 gap-3">
              {testAccounts.map(({ role, icon: Icon, color, emails }) => (
                <div key={role} className="bg-white rounded-lg p-3 border border-yellow-100">
                  <div className="flex items-center gap-1.5 mb-2">
                    <Icon size={14} className={color} />
                    <span className="text-xs font-semibold text-gray-700">{role}</span>
                  </div>
                  {emails.map(e => <p key={e} className="text-xs text-gray-500">{e}</p>)}
                </div>
              ))}
            </div>
          </div>

          {/* Tabs */}
          <div className="grid grid-cols-2 border border-gray-200 rounded-lg overflow-hidden">
            {TABS.map(t => (
              <button
                key={t}
                onClick={() => setTab(t)}
                className={`py-2.5 text-sm font-medium flex items-center justify-center gap-2 transition-colors ${tab === t ? 'bg-purple-50 text-purple-700 border-b-2 border-purple-600' : 'text-gray-600 hover:bg-gray-50'}`}
              >
                {t === 'Public User' ? <User size={14} /> : <CheckCircle size={14} className="text-green-600" />}
                {t}
              </button>
            ))}
          </div>

          {/* Tab Content */}
          {tab === 'Public User' && (
            <div className="bg-gray-50 rounded-xl p-5 space-y-4">
              <div className="flex items-center gap-2 mb-1">
                <div className="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                  <User size={16} className="text-blue-600" />
                </div>
                <h3 className="font-bold text-gray-800">Public User Role</h3>
              </div>
              <p className="text-sm text-gray-600">Public users can report stray animals and adopt from the shelter:</p>
              <div className="space-y-3">
                {publicUserFeatures.map(({ icon: Icon, title, description, linkText, end }) => (
                  <div key={title} className="flex gap-3 py-3 border-b border-gray-200 last:border-0">
                    <div className="w-8 h-8 bg-purple-100 rounded-lg flex-shrink-0 flex items-center justify-center mt-0.5">
                      <Icon size={15} className="text-purple-600" />
                    </div>
                    <div>
                      <p className="font-semibold text-gray-800 text-sm">{title}</p>
                      <p className="text-xs text-gray-500 mt-0.5">
                        {description}<span className="text-purple-600 font-medium">{linkText}</span>{end}
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {tab === 'Adoption Process' && (
            <div className="bg-gray-50 rounded-xl p-5 space-y-4">
              <div className="flex items-center gap-2 mb-1">
                <div className="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                  <CheckCircle size={16} className="text-green-600" />
                </div>
                <h3 className="font-bold text-gray-800">Adoption Process</h3>
              </div>
              <p className="text-sm text-gray-600">Follow these steps to adopt an animal from our shelter:</p>
              <div className="space-y-4">
                {adoptionSteps.map(({ step, title, description, linkText, end }) => (
                  <div key={step} className="flex gap-3">
                    <div className="w-7 h-7 bg-purple-600 rounded-full flex-shrink-0 flex items-center justify-center text-white text-xs font-bold mt-0.5">
                      {step}
                    </div>
                    <div>
                      <p className="font-semibold text-gray-800 text-sm">{title}</p>
                      <p className="text-xs text-gray-500 mt-0.5">
                        {description}
                        {linkText && <span className="text-purple-600 font-medium">{linkText}</span>}
                        {end}
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="px-6 py-4 border-t flex items-center justify-between bg-gray-50">
          <div className="flex items-center gap-2 text-gray-500 text-xs">
            <span className="text-blue-500">ℹ️</span>
            Need help? Contact your system administrator.
          </div>
          <button
            onClick={onClose}
            className="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition-colors"
          >
            Got It!
          </button>
        </div>
      </div>
    </div>
  )
}
