import { useState, useRef, useEffect, useCallback } from 'react'
import { useForm } from '@inertiajs/react'
import { X, MapPin, Navigation, Upload, ChevronDown, AlertCircle } from 'lucide-react'
import { MapContainer, TileLayer, Marker, useMapEvents } from 'react-leaflet'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// Fix leaflet default icon
delete (L.Icon.Default.prototype as any)._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
})

interface Props { onClose: () => void }

const DESCRIPTIONS = [
  'Critical - Animal injured/bleeding, needs immediate rescue',
  'High - Animal trapped or in immediate danger',
  'Normal - Stray animal sighted, appears healthy',
  'Low - Animal stray but seems safe for now',
]

const STATES = ['Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur', 'Labuan', 'Putrajaya']

function MapClickHandler({ onMapClick }: { onMapClick: (lat: number, lng: number) => void }) {
  useMapEvents({
    click: (e) => onMapClick(e.latlng.lat, e.latlng.lng),
  })
  return null
}

export default function SubmitReportModal({ onClose }: Props) {
  const fileRef = useRef<HTMLInputElement>(null)
  const [previews, setPreviews] = useState<string[]>([])
  const [toasts, setToasts] = useState<string[]>([])
  const [position, setPosition] = useState<[number, number] | null>(null)
  const [searchQuery, setSearchQuery] = useState('')
  const [gettingLocation, setGettingLocation] = useState(false)

  const form = useForm<{
    latitude: string; longitude: string; address: string; city: string; state: string
    description: string; images: File[]
  }>({
    latitude: '', longitude: '', address: '', city: '', state: '',
    description: '', images: [],
  })

  const addToast = (msg: string) => {
    setToasts(t => [...t, msg])
    setTimeout(() => setToasts(t => t.slice(1)), 3000)
  }

  const reverseGeocode = useCallback(async (lat: number, lng: number) => {
    try {
      addToast('Finding address...')
      const res = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
      const data = await res.json()
      form.setData(prev => ({
        ...prev,
        latitude: String(lat.toFixed(6)),
        longitude: String(lng.toFixed(6)),
        address: data.display_name ?? '',
        city: data.address?.city ?? data.address?.town ?? data.address?.village ?? data.address?.suburb ?? '',
        state: data.address?.state ?? '',
      }))
      addToast('Location found!')
    } catch {
      addToast('Could not fetch address.')
    }
  }, [])

  const handleMapClick = (lat: number, lng: number) => {
    setPosition([lat, lng])
    reverseGeocode(lat, lng)
  }

  const detectLocation = () => {
    if (!navigator.geolocation) { addToast('Geolocation not supported'); return }
    setGettingLocation(true)
    navigator.geolocation.getCurrentPosition(
      pos => {
        const { latitude, longitude } = pos.coords
        setPosition([latitude, longitude])
        reverseGeocode(latitude, longitude)
        setGettingLocation(false)
      },
      () => { addToast('Could not get location'); setGettingLocation(false) }
    )
  }

  const searchLocation = async () => {
    if (!searchQuery.trim()) return
    try {
      addToast('Searching...')
      const res = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(searchQuery + ', Malaysia')}&format=json&limit=1`)
      const data = await res.json()
      if (data[0]) {
        const lat = parseFloat(data[0].lat)
        const lng = parseFloat(data[0].lon)
        setPosition([lat, lng])
        reverseGeocode(lat, lng)
      } else {
        addToast('Location not found')
      }
    } catch {
      addToast('Search failed')
    }
  }

  const handleImages = (files: FileList | null) => {
    if (!files) return
    const arr = Array.from(files).slice(0, 5)
    form.setData('images', arr)
    setPreviews(arr.map(f => URL.createObjectURL(f)))
  }

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    form.post(route('reports.store'), {
      forceFormData: true,
      onSuccess: () => onClose(),
    })
  }

  const inp = 'w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent'

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
      {/* Toasts */}
      <div className="fixed top-4 right-4 z-[70] space-y-2">
        {toasts.map((t, i) => (
          <div key={i} className="bg-gray-900 text-white text-sm px-4 py-2.5 rounded-lg shadow-lg animate-pulse">{t}</div>
        ))}
      </div>

      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] overflow-hidden flex flex-col">
        {/* Header */}
        <div className="bg-gradient-to-r from-purple-600 to-indigo-700 text-white px-6 py-5 flex items-start justify-between">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
              <MapPin size={20} />
            </div>
            <div>
              <h2 className="text-xl font-bold">Submit Stray Animal Report</h2>
              <p className="text-purple-200 text-sm">Help us locate and rescue stray animals in your area</p>
            </div>
          </div>
          <button onClick={onClose} className="text-white/70 hover:text-white transition-colors mt-1">
            <X size={20} />
          </button>
        </div>

        <div className="overflow-y-auto flex-1 p-6 space-y-5">
          <form id="report-form" onSubmit={submit} className="space-y-5">
            {/* Error display */}
            {Object.keys(form.errors).length > 0 && (
              <div className="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700 space-y-1">
                <div className="flex items-center gap-2 font-semibold"><AlertCircle size={14} /> Please fix the following errors:</div>
                {Object.entries(form.errors).map(([k, v]) => <p key={k} className="pl-5">• {v}</p>)}
              </div>
            )}

            {/* Section 1: Pin Location */}
            <div className="border border-gray-200 rounded-xl p-4 space-y-3">
              <div className="flex items-center gap-2">
                <span className="w-6 h-6 bg-purple-600 text-white rounded-full text-xs font-bold flex items-center justify-center">1</span>
                <h3 className="font-semibold text-gray-800">Pin Location</h3>
              </div>

              <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">Search Location</label>
                <div className="flex gap-2">
                  <input
                    value={searchQuery}
                    onChange={e => setSearchQuery(e.target.value)}
                    onKeyDown={e => e.key === 'Enter' && (e.preventDefault(), searchLocation())}
                    placeholder="Search for city, state, or landmark..."
                    className={inp}
                  />
                  <button type="button" onClick={searchLocation} className="px-3 py-2 bg-purple-100 text-purple-700 rounded-lg text-sm font-medium hover:bg-purple-200 transition-colors">
                    Search
                  </button>
                </div>
                <p className="text-xs text-gray-400 mt-1">Type to search for a location in Malaysia</p>
              </div>

              <button
                type="button"
                onClick={detectLocation}
                disabled={gettingLocation}
                className="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors disabled:opacity-60"
              >
                <Navigation size={16} />
                {gettingLocation ? 'Getting location...' : 'Use My Current Location'}
              </button>

              {/* Leaflet Map */}
              <div className="rounded-xl overflow-hidden border border-gray-200 h-52">
                <MapContainer
                  center={position ?? [3.1390, 101.6869]}
                  zoom={position ? 15 : 10}
                  className="h-full w-full"
                >
                  <TileLayer
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                    attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                  />
                  <MapClickHandler onMapClick={handleMapClick} />
                  {position && <Marker position={position} />}
                </MapContainer>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium text-gray-600 mb-1">Latitude</label>
                  <input value={form.data.latitude} onChange={e => form.setData('latitude', e.target.value)} className={inp} placeholder="Auto-filled" readOnly />
                </div>
                <div>
                  <label className="block text-xs font-medium text-gray-600 mb-1">Longitude</label>
                  <input value={form.data.longitude} onChange={e => form.setData('longitude', e.target.value)} className={inp} placeholder="Auto-filled" readOnly />
                </div>
              </div>

              <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">Address *</label>
                <input value={form.data.address} onChange={e => form.setData('address', e.target.value)} className={inp} placeholder="Auto-filled from map" />
                {form.errors.address && <p className="mt-1 text-xs text-red-600">{form.errors.address}</p>}
              </div>
            </div>

            {/* Section 2: Location Details */}
            <div className="border border-gray-200 rounded-xl p-4 space-y-3">
              <div className="flex items-center gap-2">
                <span className="w-6 h-6 bg-purple-600 text-white rounded-full text-xs font-bold flex items-center justify-center">2</span>
                <h3 className="font-semibold text-gray-800">Location Details (Auto-filled)</h3>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium text-gray-600 mb-1">City *</label>
                  <input value={form.data.city} onChange={e => form.setData('city', e.target.value)} className={inp} placeholder="Auto-filled" />
                  <p className="text-xs text-orange-500 mt-0.5">⚠ Auto-filled based on pinned location</p>
                  {form.errors.city && <p className="mt-1 text-xs text-red-600">{form.errors.city}</p>}
                </div>
                <div>
                  <label className="block text-xs font-medium text-gray-600 mb-1">State *</label>
                  <div className="relative">
                    <select value={form.data.state} onChange={e => form.setData('state', e.target.value)} className={`${inp} appearance-none pr-8`}>
                      <option value="">Select state</option>
                      {STATES.map(s => <option key={s}>{s}</option>)}
                    </select>
                    <ChevronDown size={14} className="absolute right-2.5 top-3 text-gray-400 pointer-events-none" />
                  </div>
                  <p className="text-xs text-orange-500 mt-0.5">⚠ Auto-filled based on pinned location</p>
                  {form.errors.state && <p className="mt-1 text-xs text-red-600">{form.errors.state}</p>}
                </div>
              </div>
            </div>

            {/* Section 3: Animal Condition */}
            <div className="border border-gray-200 rounded-xl p-4 space-y-3">
              <div className="flex items-center gap-2">
                <span className="w-6 h-6 bg-green-600 text-white rounded-full text-xs font-bold flex items-center justify-center">3</span>
                <h3 className="font-semibold text-gray-800">Animal Condition & Priority</h3>
              </div>
              <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">Situation / Urgency Level *</label>
                <div className="relative">
                  <select value={form.data.description} onChange={e => form.setData('description', e.target.value)} className={`${inp} appearance-none pr-8`}>
                    <option value="">-- Select situation --</option>
                    {DESCRIPTIONS.map(d => <option key={d} value={d}>{d}</option>)}
                  </select>
                  <ChevronDown size={14} className="absolute right-2.5 top-3 text-gray-400 pointer-events-none" />
                </div>
                <p className="text-xs text-gray-400 mt-1">This helps caretakers prioritize rescues based on urgency</p>
                {form.errors.description && <p className="mt-1 text-xs text-red-600">{form.errors.description}</p>}
              </div>
            </div>

            {/* Section 4: Upload Images */}
            <div className="border border-gray-200 rounded-xl p-4 space-y-3">
              <div className="flex items-center gap-2">
                <span className="w-6 h-6 bg-orange-500 text-white rounded-full text-xs font-bold flex items-center justify-center">4</span>
                <h3 className="font-semibold text-gray-800">Upload Images</h3>
              </div>
              <div>
                <label className="block text-xs font-medium text-gray-600 mb-1">Photos of the Animal *</label>
                <div
                  onClick={() => fileRef.current?.click()}
                  className="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer hover:border-purple-400 hover:bg-purple-50 transition-colors"
                >
                  <Upload size={24} className="mx-auto text-gray-400 mb-2" />
                  <p className="text-sm text-gray-500">Click to choose files</p>
                  <p className="text-xs text-gray-400 mt-1">1–5 images, max 5MB each</p>
                </div>
                <input ref={fileRef} type="file" accept="image/*" multiple className="hidden" onChange={e => handleImages(e.target.files)} />
                {form.errors.images && <p className="mt-1 text-xs text-red-600">{form.errors.images}</p>}
              </div>
              {previews.length > 0 && (
                <div className="grid grid-cols-4 gap-2">
                  {previews.map((src, i) => <img key={i} src={src} alt="" className="h-20 w-full object-cover rounded-lg" />)}
                </div>
              )}
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
            form="report-form"
            disabled={form.processing}
            className="flex-1 bg-gradient-to-r from-purple-600 to-indigo-700 text-white py-2.5 rounded-lg text-sm font-semibold hover:from-purple-700 hover:to-indigo-800 transition-colors disabled:opacity-50"
          >
            {form.processing ? 'Submitting...' : 'Submit Report'}
          </button>
        </div>
      </div>
    </div>
  )
}
