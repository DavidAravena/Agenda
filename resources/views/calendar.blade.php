@extends(backpack_view('blank'))
@section('content')

<div class="container">
    <div class="card">
        <div class="card-body">
            <form id="form" method="POST" action="#">
                <div class="mb-3">
                    <label for="selectEspecialidad" class="form-label">Seleccionar especialidad</label>
                    <select id="selectEspecialidad" class="form-select">
                        @foreach($especialidades as $especialidad)
                        <option value="{{ $especialidad->id }}">{{ $especialidad->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="selectDoctor" class="form-label">Seleccionar profesional</label>
                    <select id="selectDoctor" class="form-select">
                        @foreach($doctores as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" id="confirmarDoctor" class="btn btn-primary">Confirmar</button>
            </form>
            <input type="hidden" id="filter-input" value="">
        </div>
    </div>
</div>
<div class="container mt-5">
    <input type="hidden" id="filter-input" value="">
    <div id='calendar'></div>
</div>

<div class="modal fade" id="modalAddEvent" tabindex="-1" aria-labelledby="modalAddEventLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddEventLabel">Agendamiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="modalAdd-body" class="modal-body">
                <form mothod="POST" id="form2" action="#">
                    @csrf
                    <div class="mb-3">
                        <label for="time">Fecha:</label>
                        <input type="datepicker" class="form-control" id="date" name="date" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="time">Hora:</label>
                        <input type="time" class="form-control" id="time" name="time" disabled>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <label class="col-md-12">??Desea confirmar su hora?</label>
                <div class="col-md-12 text-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button id="save" type="button" class="btn btn-primary">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalShowEvent" tabindex="-1" aria-labelledby="modalShowEventLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="modalShow-title" class="modal-title" id="modalShowEventLabel">Informaci??n de agendamiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="col-form-label">Doctor:</label>
                    <input type="text" class="form-control" id="doctor-show" disabled>
                </div>
                <div class="mb-3">
                    <label class="col-form-label">Especialidad:</label>
                    <input type="text" class="form-control" id="especialidad-show" disabled>
                </div>
                <div class="mb-3">
                    <label class="col-form-label">Fecha:</label>
                    <input type="text" class="form-control" id="fecha-show" disabled>
                </div>
                <div class="mb-3">
                    <label class="col-form-label">Hora:</label>
                    <input type="text" class="form-control" id="hora-show" disabled>
                </div>
            </div>
            <div class="modal-footer">
                <input type="text" id="agenda-show" hidden>
                <button type="button" onclick="cancelarHora()" class="btn btn-danger" data-bs-dismiss="modal">Cancelar hora</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {

        var selectEspecialidad = $("#selectEspecialidad");
        var selectDoctor = $('#selectDoctor');
        var especialidadId = 1;

        selectEspecialidad.change(function() {
            especialidadId = selectEspecialidad.val();

            selectDoctor.find("option").each(function() {
                $(this).remove();
            });

            $.ajax({
                url: '{{ url("/admin/services/getDoctors/especialidadId") }}/' + especialidadId,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $.each(response.data, function(key, value) {
                        selectDoctor.append("<option value='" + value.id + "'>" + value.nombre + "</option>");
                    });
                },
                error: function() {
                    alert('Hubo un error obteniendo la informaci??n.');
                }
            });
        });

        $("#confirmarDoctor").on('click', function(e) {

            if (selectDoctor.val() != null && selectEspecialidad.val() != null) {
                var doctorId = selectDoctor.val();

                $.ajax({
                    type: "GET",
                    url: '{{ url("/admin/services/getEvents") }}',
                    success: function(events) {
                        $.ajax({
                            type: "GET",
                            url: '{{ url("/admin/services/getHorario/doctor") }}/' + doctorId,
                            success: function(response) {
                                Array.prototype.push.apply(events, response['events']);
                                calendar(doctorId, events, response['businessHours']);
                            },
                            error: function() {
                                alert("Ha ocurrido un error, int??ntalo nuevamente.");
                            }
                        });
                    },
                    error: function() {
                        alert("Ha ocurrido un error, int??ntalo nuevamente.");
                    }
                });
            } else {
                alert("Debe seleccionar una especialidad y un doctor.");
            }
        });

        $('#save').click(function() {
            var data = {
                doctor_id: selectDoctor.val(),
                fecha: $('#date').val(),
                hora: $('#time').val(),
            };

            $.ajax({
                type: "POST",
                url: "{{ route('calendar.store') }}",
                data: data,
                success: function() {
                    $('#modalAddEvent').modal('hide');
                    window.location.reload();
                },
                error: function() {
                    alert("Ha ocurrido un error, int??ntalo nuevamente.");
                }
            });
        });
    });

    function calendar(doctorId = 0, events, workHours) {

        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            themeSystem: 'bootstrap',
            schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
            locale: 'cl',
            slotMinTime: '08:00:00',
            slotMaxTime: '21:00:00',
            height: 'auto',
            timeZone: 'UTC-3',
            firstDay: 1,
            allDaySlot: false,
            displayEventTime: true,
            selectable: false,
            initialView: 'timeGridWeek',
            headerToolbar: {
                start: 'title',
                end: 'today next timeGridWeek timeGridDay',
            },
            businessHours: workHours,

            buttonText: {
                today: 'hoy',
                month: 'mes',
                week: 'semana',
                day: 'd??a',
                list: 'lista',
            },

            events: events,

            eventClick: function(info) {
                $('#modalShowEvent').appendTo("body");

                var x = info.event.extendedProps.horaNoHabilitada;

                if(typeof x === 'undefined'){
                    var fecha = moment(info.event.extendedProps.fecha, 'YYYY-MM-DD').format('DD-MM-YYYY');

                    $('#doctor-show').val(info.event.extendedProps.doctor);
                    $('#especialidad-show').val(info.event.extendedProps.especialidad);
                    $('#fecha-show').val(fecha);
                    $('#hora-show').val(info.event.extendedProps.hora);
                    $('#agenda-show').val(info.event.extendedProps.agenda_id);

                    var modal = new bootstrap.Modal(document.getElementById('modalShowEvent'));
                    modal.toggle();
                }

            },

            dateClick: function(info) {
                var fecha = new Date(info.dateStr);
                var fechaAhora = new Date();

                if (info.jsEvent.originalTarget.attributes.class.nodeValue != "fc-non-business" && fecha >= fechaAhora && typeof info.jsEvent.originalTarget.attributes.style === "undefined") {
                    $('#modalAddEvent').appendTo("body");

                    var modal = new bootstrap.Modal(document.getElementById('modalAddEvent'));
                    modal.toggle();

                    var date = moment(info.dateStr, 'YYYY-MM-DDTHH:mm:ss').format('DD-MM-YYYY');
                    var startTime = moment(info.dateStr, 'YYYY-MM-DDTHH:mm:ss').format('HH:mm');

                    $('#date').val(date);
                    $('#time').val(startTime);
                }
            },
        });
        calendar.render();
    }

    function cancelarHora() {
        Swal.fire({
            title: 'Atenci??n',
            text: "??Est??s seguro de que deseas cancelar esta hora?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'S??, estoy seguro'
        }).then((result) => {
            if (result.isConfirmed) {
                var data = {
                    agenda_id: $('#agenda-show').val(),
                };
                $.ajax({
                    type: "POST",
                    url: "{{ route('calendar.delete') }}",
                    data: data,
                    success: function() {

                        $('#modalAddEvent').modal('hide');
                        window.location.reload();

                        Swal.fire(
                            'Eliminado correctamente',
                            '',
                            'success'
                        )
                    },
                    error: function() {
                        alert("Ha ocurrido un error, int??ntalo nuevamente.");
                    }
                });
            }
        });        
    }
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.6.0/main.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
<link href='https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.css' rel='stylesheet' />
<link href='https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.13.1/css/all.css' rel='stylesheet'>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar-scheduler@5.6.0/main.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection