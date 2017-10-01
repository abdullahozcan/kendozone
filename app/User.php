<?php

namespace App;

use App\Exceptions\NotOwningAssociationException;
use App\Exceptions\NotOwningClubException;
use App\Exceptions\NotOwningFederationException;
use App\Traits\RoleTrait;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Passport\HasApiTokens;
use OwenIt\Auditing\AuditingTrait;
use Thomaswelton\LaravelGravatar\Facades\Gravatar;

/**
 * @property  mixed name
 * @property  mixed email
 * @property  mixed password
 * @property bool verified
 * @property mixed token
 * @property  mixed clearPassword
 * @property string firstname
 * @property string lastname
 * @property Federation federationOwned
 * @property Association associationOwned
 * @property Club clubOwned
 * @property int id
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, SoftDeletes, Sluggable, AuditingTrait, Notifiable, HasApiTokens, Impersonate, RoleTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'email'
            ]
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'password_confirmation'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Boot the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            $softDeletedUser = User::onlyTrashed()->where('email', '=', $user->email)->first();
            if ($softDeletedUser != null) {
                $softDeletedUser->restore();
                return false;
            }
            $user->token = str_random(30);
            if ($user->country_id == 0) {
                $user->addGeoData();
            }
            return true;
        });

        // If a User is deleted, you must delete:
        // His tournaments, his competitors

        static::deleting(function ($user) {
            $user->tournaments->each->delete();
            $user->competitors->each->delete();

        });
        static::restoring(function ($user) {
            $user->competitors()->withTrashed()->get()->each->restore();
            $user->tournaments()->withTrashed()->get()->each->restore();
        });
    }


    /**
     * Add geoData based on IP
     */
    public function addGeoData()
    {
        $ip = request()->ip();
        $location = geoip($ip);
        $country = Country::where('name', '=', $location->country)->first();
        if (is_null($country)) {
            $this->country_id = config('constants.COUNTRY_ID_DEFAULT');
            $this->city = "Paris";
            $this->latitude = 48.858222;
            $this->longitude = 2.2945;
            return;
        }
        $this->country_id = $country->id;
        $this->city = $location['city'];
        $this->latitude = $location['lat'];
        $this->longitude = $location['lon'];
    }

    /**
     * Confirm the user.
     *
     * @return void
     */
    public function confirmEmail()
    {
        $this->verified = true;
        $this->token = null;
        $this->save();
    }





    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function grade()
    {
        return $this->belongsTo('App\Grade');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function settings()
    {
        return $this->hasOne('App\Settings');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invites()
    {
        return $this->hasMany('App\Invite', 'email', 'email');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo('Webpatser\Countries\Countries');
    }

    /**
     * Get all user's created (owned) tournmanents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tournaments()
    {
        return $this->hasMany('App\Tournament');
    }

    /**
     * Get all deleted user's tournmanents
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tournamentsDeleted()
    {
        return $this->hasMany('App\Tournament')->onlyTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function championships()
    {
        return $this->belongsToMany(Championship::class, 'competitor')
            ->withTimestamps();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function federation()
    {
        return $this->belongsTo(Federation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function association()
    {
        return $this->belongsTo(Association::class);
    }

    /**
     * A president of federation owns a federation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function federationOwned()
    {
        return $this->belongsTo(Federation::class, 'id', 'president_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function associationOwned()
    {
        return $this->belongsTo(Association::class, 'id', 'president_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function clubOwned()
    {
        return $this->belongsTo(Club::class, 'id', 'president_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function competitors()
    {
        return $this->hasMany(Competitor::class);
    }

    /**
     * Tournament where I have participated as competitor
     * @return mixed
     */
    public function myTournaments()
    {
        return Tournament::leftJoin('championship', 'championship.tournament_id', '=', 'tournament.id')
            ->leftJoin('competitor', 'competitor.championship_id', '=', 'championship.id')
            ->where('competitor.user_id', '=', $this->id)
            ->select('tournament.*')
            ->distinct();
    }


    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted_at != null;
    }

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @param $firstname
     * @param $lastname
     */
    public function updateUserFullName($firstname, $lastname)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->save();
    }


    /**
     * @return Collection
     */
    public function fillSelect()
    {
        $users = new Collection();
        if ($this->isSuperAdmin()) {
            $users = User::pluck('name', 'id')->prepend('-', 0);
        } else if ($this->isFederationPresident() && $this->federationOwned != null) {
            $users = User::where('federation_id', '=', $this->federationOwned->id)->pluck('name', 'id')->prepend('-', 0);
        } else if ($this->isAssociationPresident() && $this->associationOwned != null) {
            $users = User::where('association_id', '=', $this->associationOwned->id)->pluck('name', 'id')->prepend('-', 0);
        } else if ($this->isClubPresident() && $this->clubOwned != null) {
            $users = User::where('club_id', '=', $this->clubOwned->id)->pluck('name', 'id')->prepend('-', 0);
        }
        return $users;
    }

    /**
     * @return Collection
     */
    public function getClubPresidentsList()
    {
        $users = new Collection();
        if ($this->isSuperAdmin()) {
            $users = User::pluck('name', 'id');
        } else if ($this->isFederationPresident() && $this->federationOwned != null) {
            $users = User::federationPresident(Auth::user())->pluck('name', 'id')->prepend('-', 0);
        } else if ($this->isAssociationPresident() && $this->associationOwned != null) {
            $users = User::associationPresident(Auth::user())->pluck('name', 'id')->prepend('-', 0);
        } else if ($this->isClubPresident() && $this->clubOwned != null) {
            $users = User::clubPresident(Auth::user())->pluck('name', 'id')->prepend('-', 0);
        }
        return $users;

    }

    /**
     * Check if a user is registered to a tournament
     * @param Tournament $tournament
     * @return bool
     */
    public function isRegisteredTo(Tournament $tournament)
    {
        $ids = $tournament->championships->pluck('id');
        $isRegistered = Competitor::where('user_id', $this->id)
            ->whereIn('championship_id', $ids)
            ->get();

        return sizeof($isRegistered) > 0;
    }

    /**
     * @return bool
     */
    public function canImpersonate()
    {
        return $this->isSuperAdmin();
    }

    /**
     * @return bool
     */
    public function canBeImpersonated()
    {
        return $this->isSuperAdmin();
    }


    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstname ?? '' . " " . $this->lastname ?? '';
    }

    /**
     * @param $query
     * @param User $user
     * @return Builder
     * @throws AuthorizationException
     * @throws NotOwningAssociationException
     * @throws NotOwningClubException
     * @throws NotOwningFederationException
     */
    public function scopeForUser($query, User $user)
    {
        // If user manage a structure, he will be limited to see entity of this structure
        // If user has the role but manage no structure --> AuthorizationException
        switch (true) {
            case $user->isSuperAdmin():
                return $query;
            case $user->isFederationPresident() && $user->federationOwned != null:
                return $query->federationPresident($user);
            case $user->isAssociationPresident() && $user->associationOwned:
                return $query->associationPresident($user);
            case $user->isClubPresident() && $user->clubOwned != null:
                return $query->clubPresident($user);
            case $user->isFederationPresident() && !$user->federationOwned != null:
                throw new NotOwningFederationException();
            case $user->isAssociationPresident() && !$user->associationOwned:
                throw new NotOwningAssociationException();
            case $user->isClubPresident() && $user->clubOwned == null:
                throw new NotOwningClubException();
            default:
                throw new AuthorizationException();
        }
    }

    public function scopeFederationPresident($query, User $user)
    {
        return $query->where('federation_id', '=', $user->federationOwned->id);
    }

    public function scopeAssociationPresident($query, User $user)
    {
        return $query->where('association_id', '=', $user->associationOwned->id);
    }

    public function scopeClubPresident($query, User $user)
    {
        return $query->where('club_id', '=', $user->clubOwned->id);
    }


    public function getAvatarAttribute($avatar)
    {
        if (!isset($avatar) && Gravatar::exists($this->email)) {
            return Gravatar::src($this->email);

        }

        if (!str_contains($avatar, 'http') && isset($avatar)) {
            return config('constants.AVATAR_PATH') . $avatar;
        }
        return $avatar;
    }


}
