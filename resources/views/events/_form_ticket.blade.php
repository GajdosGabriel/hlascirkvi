
<div class="card">

    <div class="card_header">
        <h4>Rezervácia miesta</h4>
    </div>
    <div class="card-body">
        <!-- Tickets-->

        <div class="card-body px-4">
            <div class="font-semibold">{{ $event->title }}</div>
            <div class="mb-6">
                <div>Zadarmo</div>
                <!--<label>Početmiest</label>-->
                <select v-model.number="quantities" class="form-control">
                    <option disabled value="">Počet miest</option>
                    <option value="1">1 miesto</option>
                    <option value="2">2 miesta</option>
                    <option value="3">3 miesta</option>
                    <option value="4">4 miesta</option>
                    <option value="5">5 miest</option>
                    <option value="6">6 miest</option>
                    <option value="7">7 miest</option>
                </select>
            </div>

        </div>

        <form method="post" action="{{ route('event.subscribeByForm', [$event->id]) }}" >
            @csrf @method('POST')

            <div v-for="ticket in quantities" v-cloak class="bg-gray-300 mb-6">
                <div class="px-4">
                    <div class="pb-3">
                        <div class="mb-4 font-semibold text-2xl">
                            <span>Rezervácia miesta na akciu "{{ $event->title }}"</span>
                            <span>Zadarmo</span>
                        </div>

                        <div class="form-group">
                            <label>Meno</label>
                            <input type="text" name="first_name[]" placeholder="Meno prihláseného" required class="form-control" >
                        </div>
                        <div class="form-group">
                            <label>Priezvisko</label>
                            <input type="text" name="last_name[]" placeholder="Priezvisko prihláseného" required class="form-control" >
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email[]" placeholder="Email na potvrdenie rezervácie" required class="form-control" >
                        </div>

                        <input type="checkbox" name="love" value="love" id="love" required>
                        <label style="background: white; color: #8e8e8e" for="love"> Súhlas z spracovaním osob. údajov</label>

                    </div>
                </div>
            </div>

            <div class="form-group text-right px-4">
                <button type="submit" class="btn btn-primary" :class="disabled" >Registrovať</button>
            </div>
        </form>

    </div>

</div>


