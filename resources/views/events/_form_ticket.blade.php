
<div class="card">

    <div class="level card-header" style="border-bottom: .1rem solid silver; padding-bottom: .5rem; margin-bottom: 2rem">
        <span style="font-weight: 600">Rezervácia miesta</span>
    </div>
    <div class="card-body">
        <!-- Tickets-->

        <div class="card-body level" style="border: solid .1rem silver;padding: 2rem 1rem;margin-bottom: 2rem">
            <div style="font-weight: 600">{{ $event->title }}</div>
            <div class="inline">
                <div style="margin-right: 1rem">Zadarmo</div>
                <!--<label>Početmiest</label>-->
                <select v-model.number="quantities">
                    <option disabled value="">Počet miest</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                </select>
            </div>

        </div>

        <form method="post" action="{{ route('event.subscribeByForm', [$event->id]) }}" >
            @csrf

            <div v-for="ticket in quantities" v-cloak>
                <div class="card">
                    <div class="card-body">
                        <div class="level" style="background: #3b5999; color: whitesmoke;margin: -1rem;padding: 1rem;margin-bottom: 1rem;">
                            <span>Rezervácia miesta na akciu "{{ $event->title }}"</span>
                            <span>Zadarmo</span>
                        </div>

                        <div class="form-group inline">
                            <label>Meno</label>
                            <input type="text" name="first_name[]" placeholder="Meno prihláseného" required>
                        </div>
                        <div class="form-group inline">
                            <label>Priezvisko</label>
                            <input type="text" name="last_name[]" placeholder="Priezvisko prihláseného" required>
                        </div>

                        <div class="form-group inline">
                            <label>Email</label>
                            <input type="email" name="email[]" placeholder="Email prihláseneho" required>
                        </div>

                        <input type="checkbox" name="love" value="love" id="love" required>
                        <label style="background: white; color: #8e8e8e" for="love"> Súhlas z spracovaním osob. údajov</label>

                    </div>
                </div>
            </div>

            <div class="form-group" style="text-align: right">
                <button type="submit" class="btn btn-small" :class="disabled" >Registrovať</button>
            </div>
        </form>

    </div>

</div>


