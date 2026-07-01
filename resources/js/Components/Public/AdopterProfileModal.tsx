import { useForm } from '@inertiajs/react'
import { X, User, CheckCircle, AlertCircle } from 'lucide-react'

interface AdopterProfile {
  housing_type?: string
  has_children?: boolean
  has_other_pets?: boolean
  activity_level?: string
  pet_experience?: string
  preferred_species?: string
  preferred_size?: string
}

interface Props {
  profile: AdopterProfile | null
  onClose: () => void
}

const sel = 'w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white'

export default function AdopterProfileModal({ profile, onClose }: Props) {
  const form = useForm({
    housing_type: profile?.housing_type ?? '',
    has_children: profile?.has_children ? 'Yes' : (profile?.has_children === false ? 'No' : ''),
    has_other_pets: profile?.has_other_pets ? 'Yes' : (profile?.has_other_pets === false ? 'No' : ''),
    activity_level: profile?.activity_level ?? '',
    pet_experience: profile?.pet_experience ?? '',
    preferred_species: profile?.preferred_species ?? '',
    preferred_size: profile?.preferred_size ?? '',
  })

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    form.post(route('adopter.profile.store'), {
      onSuccess: () => onClose(),
    })
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[92vh] overflow-hidden flex flex-col">
        {/* Header */}
        <div className="bg-gradient-to-r from-purple-600 to-indigo-700 text-white px-6 py-5 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
              <User size={20} />
            </div>
            <h2 className="text-xl font-bold">Adopter Profile</h2>
          </div>
          <button onClick={onClose} className="text-white/70 hover:text-white transition-colors">
            <X size={20} />
          </button>
        </div>

        <div className="overflow-y-auto flex-1 p-6 space-y-5">
          {/* Info Box */}
          <div className="bg-blue-50 border border-blue-200 rounded-xl p-4 space-y-2">
            <div className="flex items-center gap-2">
              <AlertCircle size={16} className="text-blue-600 flex-shrink-0" />
              <span className="font-semibold text-blue-800 text-sm">Create Your Adopter Profile</span>
            </div>
            <p className="text-xs text-blue-700 leading-relaxed">
              Help us find your perfect companion! Complete this profile so our intelligent matching system can recommend animals that best fit your lifestyle and home environment.
            </p>
            <div className="grid grid-cols-2 gap-2 mt-2">
              {[
                ['Personalized Matches', 'Get compatibility scores based on your preferences'],
                ['Save Time', 'See only animals compatible with your living situation'],
                ['Better Outcomes', 'Find animals suited to your experience level'],
                ['Happy Adoptions', 'Increase success with well-matched placements'],
              ].map(([title, desc]) => (
                <div key={title} className="flex items-start gap-1.5">
                  <CheckCircle size={12} className="text-blue-500 flex-shrink-0 mt-0.5" />
                  <p className="text-xs text-blue-700"><span className="font-semibold">{title}:</span> {desc}</p>
                </div>
              ))}
            </div>
          </div>

          {/* Success Flash */}
          {profile && (
            <div className="flex items-center gap-2 bg-green-50 border border-green-200 rounded-lg px-4 py-3">
              <CheckCircle size={16} className="text-green-600" />
              <span className="text-green-800 text-sm font-medium">Adopter profile created successfully</span>
            </div>
          )}

          {/* Form */}
          <form id="adopter-form" onSubmit={submit} className="space-y-4">
            {form.errors.housing_type && <p className="text-xs text-red-600">{form.errors.housing_type}</p>}

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Housing Type</label>
              <select value={form.data.housing_type} onChange={e => form.setData('housing_type', e.target.value)} className={sel}>
                <option value="">Select housing type</option>
                {['Apartment', 'House', 'Condo', 'Townhouse', 'Farm'].map(o => <option key={o}>{o}</option>)}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Do you have children?</label>
              <select value={form.data.has_children} onChange={e => form.setData('has_children', e.target.value)} className={sel}>
                <option value="">Select</option>
                <option>Yes</option>
                <option>No</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Do you have other pets?</label>
              <select value={form.data.has_other_pets} onChange={e => form.setData('has_other_pets', e.target.value)} className={sel}>
                <option value="">Select</option>
                <option>Yes</option>
                <option>No</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Activity Level</label>
              <select value={form.data.activity_level} onChange={e => form.setData('activity_level', e.target.value)} className={sel}>
                <option value="">Select activity level</option>
                {['Low - Mostly sedentary', 'Medium - Moderate exercise and play', 'High - Very active lifestyle'].map(o => <option key={o}>{o}</option>)}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Pet Experience</label>
              <select value={form.data.pet_experience} onChange={e => form.setData('pet_experience', e.target.value)} className={sel}>
                <option value="">Select experience level</option>
                {['Beginner', 'Intermediate', 'Experienced'].map(o => <option key={o}>{o}</option>)}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Preferred Species</label>
              <select value={form.data.preferred_species} onChange={e => form.setData('preferred_species', e.target.value)} className={sel}>
                <option value="">Any species</option>
                {['Dog', 'Cat', 'Rabbit', 'Bird', 'Other'].map(o => <option key={o}>{o}</option>)}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1.5">Preferred Size</label>
              <select value={form.data.preferred_size} onChange={e => form.setData('preferred_size', e.target.value)} className={sel}>
                <option value="">Any size</option>
                {['Small', 'Medium', 'Large'].map(o => <option key={o}>{o}</option>)}
              </select>
            </div>
          </form>
        </div>

        {/* Footer */}
        <div className="px-6 py-4 border-t flex gap-3 bg-gray-50">
          <button onClick={onClose} className="flex-1 border border-gray-300 text-gray-700 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-100 transition-colors">
            Cancel
          </button>
          <button
            type="submit"
            form="adopter-form"
            disabled={form.processing}
            className="flex-1 bg-gradient-to-r from-purple-600 to-indigo-700 text-white py-2.5 rounded-lg text-sm font-semibold hover:from-purple-700 hover:to-indigo-800 transition-colors disabled:opacity-50"
          >
            {form.processing ? 'Saving...' : 'Save Profile'}
          </button>
        </div>
      </div>
    </div>
  )
}
