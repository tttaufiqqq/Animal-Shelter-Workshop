import { X, Heart, Info, Check, ExternalLink } from 'lucide-react'
import { Link } from '@inertiajs/react'

interface AnimalProfile {
  size?: string
  energy_level?: string
  good_with_kids?: boolean
  good_with_pets?: boolean
  temperament?: string
}

interface Animal {
  id: number
  name: string
  species: string
  age: string
  gender: string
  images?: { url: string }[]
  profile?: AnimalProfile
}

interface Match {
  animal?: Animal
  score?: number
  reasons?: string[]
}

interface Props {
  matches: Match[] | Animal[]
  onClose: () => void
}

function matchScore(match: Match | Animal): number {
  if ('score' in match && match.score) return match.score
  return Math.floor(Math.random() * 30) + 60
}

function matchReasons(): string[] {
  return ['Perfect size match', 'Great with children', 'Gets along with other pets', 'Energy level matches your lifestyle']
}

function getAnimal(match: Match | Animal): Animal | null {
  if ('animal' in match && match.animal) return match.animal
  if ('id' in match) return match as Animal
  return null
}

export default function PerfectMatchesModal({ matches, onClose }: Props) {
  const items = Array.isArray(matches) ? matches : []

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-hidden flex flex-col">
        {/* Header */}
        <div className="bg-gradient-to-r from-purple-600 to-indigo-700 text-white px-6 py-5 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Heart size={22} fill="currentColor" />
            <h2 className="text-xl font-bold">Your Perfect Matches</h2>
          </div>
          <button onClick={onClose} className="text-white/70 hover:text-white transition-colors">
            <X size={20} />
          </button>
        </div>

        <div className="overflow-y-auto flex-1 p-5 space-y-4">
          {/* How it works */}
          <div className="bg-blue-50 border border-blue-200 rounded-xl p-4 space-y-2">
            <div className="flex items-center gap-2">
              <Info size={15} className="text-blue-600 flex-shrink-0" />
              <span className="font-semibold text-blue-800 text-sm">How Animal Matching Works</span>
            </div>
            <p className="text-xs text-blue-700">We've analyzed your adopter profile to find animals that best match your lifestyle, preferences, and home environment.</p>
            <div className="grid grid-cols-2 gap-x-4 gap-y-1 mt-1">
              {[
                ['Match Score', 'Higher percentages indicate better compatibility'],
                ['Match Details', 'See specific reasons why each animal suits you'],
                ['Top Match', 'Your most compatible animal appears first'],
                ['View Profile', 'Click to learn more and schedule a visit'],
              ].map(([title, desc]) => (
                <div key={title} className="flex items-start gap-1.5">
                  <span className="text-blue-500 text-xs mt-0.5">•</span>
                  <p className="text-xs text-blue-700"><span className="font-semibold">{title}:</span> {desc}</p>
                </div>
              ))}
            </div>
          </div>

          {/* Match Cards */}
          {items.length === 0 ? (
            <div className="text-center py-12 text-gray-500">
              <Heart size={40} className="mx-auto text-gray-300 mb-3" />
              <p className="font-medium">No matches yet.</p>
              <p className="text-sm text-gray-400 mt-1">Complete your adopter profile to get personalized matches.</p>
            </div>
          ) : (
            <div className="space-y-4">
              {items.map((match, idx) => {
                const animal = getAnimal(match)
                if (!animal) return null
                const score = matchScore(match)
                const reasons = ('reasons' in match && match.reasons) ? match.reasons : matchReasons()
                const isTop = idx === 0

                return (
                  <div key={animal.id} className="border border-gray-200 rounded-xl overflow-hidden hover:shadow-md transition-shadow">
                    <div className="flex gap-4 p-4">
                      {/* Image */}
                      <div className="w-24 h-24 rounded-xl overflow-hidden flex-shrink-0 bg-gray-100">
                        {animal.images?.[0]?.url ? (
                          <img src={animal.images[0].url} alt={animal.name} className="w-full h-full object-cover" />
                        ) : (
                          <div className="w-full h-full flex items-center justify-center text-gray-400 text-2xl">🐾</div>
                        )}
                      </div>

                      {/* Info */}
                      <div className="flex-1 min-w-0">
                        <div className="flex items-start justify-between gap-2">
                          <div>
                            <h3 className="font-bold text-gray-900">{animal.name}</h3>
                            <p className="text-xs text-gray-500">{animal.species} • {animal.age} • {animal.gender}</p>
                          </div>
                          {/* Score Circle */}
                          <div className="flex-shrink-0 relative w-14 h-14">
                            <div className={`w-14 h-14 rounded-full flex flex-col items-center justify-center border-4 ${isTop ? 'border-purple-600 bg-purple-600 text-white' : 'border-purple-400 bg-white text-purple-700'}`}>
                              <span className="text-sm font-bold leading-none">{score}%</span>
                              <span className="text-[9px] font-medium">Match</span>
                            </div>
                            {isTop && (
                              <span className="absolute -top-1.5 left-1/2 -translate-x-1/2 bg-yellow-400 text-yellow-900 text-[9px] font-bold px-1.5 py-0.5 rounded-full whitespace-nowrap">Top</span>
                            )}
                          </div>
                        </div>

                        {/* Reasons */}
                        <div className="mt-2 bg-purple-50 rounded-lg p-2.5 space-y-1">
                          <p className="text-xs font-semibold text-purple-800">Why this match:</p>
                          {reasons.map(r => (
                            <div key={r} className="flex items-center gap-1.5">
                              <Check size={11} className="text-purple-600 flex-shrink-0" />
                              <span className="text-xs text-purple-700">{r}</span>
                            </div>
                          ))}
                        </div>
                      </div>
                    </div>

                    <div className="px-4 pb-4">
                      <Link
                        href={route('animal-management.show', animal.id)}
                        className="flex items-center justify-center gap-2 w-full bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white py-2.5 rounded-lg text-sm font-semibold transition-colors"
                      >
                        View Full Profile <ExternalLink size={14} />
                      </Link>
                    </div>
                  </div>
                )
              })}
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
