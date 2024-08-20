<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $cmdr_name
 * @property string|null $inara_api_key
 * @property string|null $edsm_api_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FleetCarrier> $carriers
 * @property-read int|null $carriers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FleetCarrierJourneySchedule> $carriersJourneySchedule
 * @property-read int|null $carriers_journey_schedule_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\CommanderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Commander newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Commander newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Commander query()
 * @method static \Illuminate\Database\Eloquent\Builder|Commander whereCmdrName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commander whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commander whereEdsmApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commander whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commander whereInaraApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commander whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commander whereUserId($value)
 */
	class Commander extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property int $commander_id
 * @property string $identifier
 * @property int $has_refuel
 * @property int $has_repair
 * @property int $has_armory
 * @property int $has_shipyard
 * @property int $has_outfitting
 * @property int $has_cartographics
 * @property string|null $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FleetCarrierJourneySchedule> $carrierJourneySchedule
 * @property-read int|null $carrier_journey_schedule_count
 * @property-read \App\Models\Commander $commander
 * @method static \Database\Factories\FleetCarrierFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier filter(array $options, bool $exact)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier query()
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereCommanderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereHasArmory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereHasCartographics($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereHasOutfitting($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereHasRefuel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereHasRepair($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereHasShipyard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrier withoutTrashed()
 */
	class FleetCarrier extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $fleet_carrier_id
 * @property int $departure_system_id
 * @property int $destination_system_id
 * @property string $title
 * @property string|null $description
 * @property string $departs_at
 * @property string|null $departed_at
 * @property string|null $arrives_at
 * @property string|null $arrived_at
 * @property int $is_boarding
 * @property int $is_cancelled
 * @property int $has_departed
 * @property int $has_arrived
 * @property string|null $slug
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\FleetCarrier $carrier
 * @property-read \App\Models\System $departure
 * @property-read \App\Models\System $destination
 * @method static \Database\Factories\FleetCarrierJourneyScheduleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule filter(array $options, bool $exact)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereArrivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereArrivesAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereDepartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereDepartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereDepartureSystemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereDestinationSystemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereFleetCarrierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereHasArrived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereHasDeparted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereIsBoarding($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereIsCancelled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|FleetCarrierJourneySchedule withoutTrashed()
 */
	class FleetCarrierJourneySchedule extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $uploaded_at
 * @property string $banner_image
 * @property string|null $slug
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews query()
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews whereBannerImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews whereUploadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|GalnetNews withoutTrashed()
 */
	class GalnetNews extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $id64
 * @property string $name
 * @property string $coords
 * @property int|null $body_count
 * @property string|null $slug
 * @property string $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FleetCarrierJourneySchedule> $arrivals
 * @property-read int|null $arrivals_count
 * @property-read mixed $bodies
 * @property-read int|null $bodies_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FleetCarrierJourneySchedule> $departures
 * @property-read int|null $departures_count
 * @property-read \App\Models\SystemInformation|null $information
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SystemStation> $stations
 * @property-read int|null $stations_count
 * @method static \Illuminate\Database\Eloquent\Builder|System filter(array $options, bool $exact)
 * @method static \Illuminate\Database\Eloquent\Builder|System findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|System newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|System newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|System onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|System query()
 * @method static \Illuminate\Database\Eloquent\Builder|System whereBodyCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|System whereCoords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|System whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|System whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|System whereId64($value)
 * @method static \Illuminate\Database\Eloquent\Builder|System whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|System whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|System whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|System withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|System withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|System withoutTrashed()
 */
	class System extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $id64
 * @property int $body_id
 * @property int $system_id
 * @property string $name
 * @property string $discovered_by
 * @property string $discovered_at
 * @property string $type
 * @property string $sub_type
 * @property int|null $distance_to_arrival
 * @property int|null $is_main_star
 * @property int|null $is_scoopable
 * @property string|null $spectral_class
 * @property string|null $luminosity
 * @property float|null $solar_masses
 * @property float|null $solar_radius
 * @property float|null $absolute_magnitude
 * @property int|null $surface_temp
 * @property float|null $radius
 * @property float|null $gravity
 * @property float|null $earth_masses
 * @property string|null $atmosphere_type
 * @property string|null $volcanism_type
 * @property string|null $terraforming_state
 * @property int $is_landable
 * @property float|null $orbital_period
 * @property float|null $orbital_eccentricity
 * @property float|null $orbital_inclination
 * @property float|null $arg_of_periapsis
 * @property float|null $rotational_period
 * @property int $is_tidally_locked
 * @property float|null $semi_major_axis
 * @property float|null $axial_tilt
 * @property string|null $rings
 * @property string|null $parents
 * @property string|null $slug
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\System $system
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody filter(array $options, bool $exact)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody query()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereAbsoluteMagnitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereArgOfPeriapsis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereAtmosphereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereAxialTilt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereBodyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereDiscoveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereDiscoveredBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereDistanceToArrival($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereEarthMasses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereGravity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereId64($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereIsLandable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereIsMainStar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereIsScoopable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereIsTidallyLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereLuminosity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereOrbitalEccentricity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereOrbitalInclination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereOrbitalPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereParents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereRadius($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereRings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereRotationalPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereSemiMajorAxis($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereSolarMasses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereSolarRadius($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereSpectralClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereSubType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereSurfaceTemp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereSystemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereTerraformingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody whereVolcanismType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemBody withoutTrashed()
 */
	class SystemBody extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $system_id
 * @property string|null $allegiance
 * @property string|null $government
 * @property string|null $faction
 * @property string|null $faction_state
 * @property int $population
 * @property string|null $security
 * @property string|null $economy
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\System $system
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation query()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation whereAllegiance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation whereEconomy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation whereFaction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation whereFactionState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation whereGovernment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation wherePopulation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation whereSecurity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation whereSystemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemInformation whereUpdatedAt($value)
 */
	class SystemInformation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $system_id
 * @property int $market_id
 * @property string $type
 * @property string $name
 * @property string|null $body
 * @property int $distance_to_arrival
 * @property string|null $allegiance
 * @property string|null $government
 * @property string|null $economy
 * @property string|null $second_economy
 * @property int $has_market
 * @property int $has_shipyard
 * @property int $has_outfitting
 * @property string|null $other_services
 * @property string|null $controlling_faction
 * @property string|null $information_last_updated
 * @property string|null $market_last_updated
 * @property string|null $shipyard_last_updated
 * @property string|null $outfitting_last_updated
 * @property string|null $slug
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\System $system
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation filter(array $options, bool $exact)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation query()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereAllegiance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereControllingFaction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereDistanceToArrival($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereEconomy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereGovernment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereHasMarket($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereHasOutfitting($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereHasShipyard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereInformationLastUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereMarketId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereMarketLastUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereOtherServices($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereOutfittingLastUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereSecondEconomy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereShipyardLastUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereSystemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemStation withoutTrashed()
 */
	class SystemStation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Commander|null $commander
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

