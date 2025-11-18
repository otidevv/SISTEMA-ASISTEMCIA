@extends('layouts.app')

@section('title', 'Tarjetas Pre Universitario')

@push('css')
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .tarjeta {
            width: 8.5cm;
            height: 5.5cm;
            border: 1px solid #000;
            margin: 10px;
            padding: 10px;
            display: inline-block;
            background-color: #f8f9fa;
            position: relative;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .tarjeta-p { background-color: #007bff; color: white; }
        .tarjeta-q { background-color: #28a745; color: white; }
        .tarjeta-r { background-color: #ffc107; color: black; }
        .tarjetas-container {
            column-count: 2;
            column-gap: 20px;
        }
        @media print {
            .no-print { display: none; }
            .tarjeta { page-break-inside: avoid; }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Tarjetas de Identificación - Centro Pre Universitario UNAMAD</h4>
                    </div>
                    <div class="card-body">
                        <div id="react-root"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://unpkg.com/react@17/umd/react.development.js"></script>
    <script src="https://unpkg.com/react-dom@17/umd/react-dom.development.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

    <script>
        const CARGAR_DATOS_URL = '{{ url('api/tarjetas-preuni') }}';
    </script>

    @verbatim
    <script type="text/babel" data-type="module">
        const { useState, useEffect } = React;

        function App() {
            const [postulantes, setPostulantes] = useState([]);
            const [loading, setLoading] = useState(false);

            const cargarDatos = async () => {
                setLoading(true);
                try {
                    const response = await axios.get(CARGAR_DATOS_URL);
                    setPostulantes(response.data);
                } catch (error) {
                    console.error('Error al cargar datos:', error);
                    const errorMessage = error.response && error.response.data && error.response.data.error
                        ? error.response.data.error
                        : 'Error al cargar los datos. Revise la consola para más detalles.';
                    alert(errorMessage);
                } finally {
                    setLoading(false);
                }
            };

            const imprimir = () => {
                window.print();
            };

            const exportarPDF = async (postulantes) => {
                try {
                    const response = await axios.post('{{ route("tarjetas.exportar-pdf") }}', {
                        postulantes: postulantes
                    }, {
                        responseType: 'blob'
                    });

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'tarjetas_preuni_' + new Date().toISOString().split('T')[0] + '.pdf');
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                } catch (error) {
                    console.error('Error al exportar PDF:', error);
                    alert('Error al exportar PDF. Revise la consola para más detalles.');
                }
            };

            useEffect(() => {
                // Cargar datos automáticamente al montar el componente
                cargarDatos();
            }, []);

            return (
                <div>
                    <div className="no-print mb-3">
                        <button
                            onClick={cargarDatos}
                            className="btn btn-primary"
                            disabled={loading}
                        >
                            {loading ? 'Cargando...' : 'Cargar Datos'}
                        </button>
                        <button
                            onClick={imprimir}
                            className="btn btn-success ml-2"
                            disabled={postulantes.length === 0}
                        >
                            Imprimir
                        </button>
                        <button
                            onClick={() => exportarPDF(postulantes)}
                            className="btn btn-danger ml-2"
                            disabled={postulantes.length === 0}
                        >
                            <i className="fas fa-file-pdf"></i> Exportar PDF
                        </button>
                    </div>

                    <div id="tarjetas-container" className="tarjetas-container">
                        {postulantes.map((postulante, index) => (
                            <Tarjeta key={index} postulante={postulante} />
                        ))}
                    </div>
                </div>
            );
        }

        function Tarjeta({ postulante }) {
            const getClaseTema = (tema) => {
                switch(tema) {
                    case 'P': return 'tarjeta-p';
                    case 'Q': return 'tarjeta-q';
                    case 'R': return 'tarjeta-r';
                    default: return 'tarjeta-r';
                }
            };

            const centerStyle = { textAlign: 'center', fontWeight: 'bold', fontSize: '14px' };
            const centerStyle2 = { textAlign: 'center', fontWeight: 'bold', marginBottom: '10px' };
            const flexStyle = { display: 'flex', justifyContent: 'space-between', marginBottom: '5px' };
            const flexStyle2 = { display: 'flex', justifyContent: 'space-between' };

            return (
                <div className={`tarjeta ${getClaseTema(postulante.tema)}`}>
                    <div style={centerStyle}>
                        UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS
                    </div>
                    <div style={centerStyle2}>
                        CENTRO PRE UNIVERSITARIO
                    </div>
                    {postulante.foto && (
                        <div style={{ textAlign: 'center', marginBottom: '10px' }}>
                            <img
                                src={postulante.foto}
                                alt="Foto del estudiante"
                                style={{
                                    width: '60px',
                                    height: '60px',
                                    objectFit: 'cover',
                                    borderRadius: '50%',
                                    border: '2px solid #fff',
                                    boxShadow: '0 2px 4px rgba(0,0,0,0.2)'
                                }}
                            />
                        </div>
                    )}
                    <div style={flexStyle}>
                        <span><strong>GRUPO:</strong> {postulante.grupo}</span>
                        <span><strong>TEMA:</strong> {postulante.tema}</span>
                        <span><strong>CÓDIGO:</strong> {postulante.codigo}</span>
                    </div>
                    <div style={flexStyle2}>
                        <span><strong>AULA:</strong> {postulante.aula}</span>
                        <span><strong>CARRERA:</strong> {postulante.carrera}</span>
                        <span><strong>NOMBRES:</strong> {postulante.nombres}</span>
                    </div>
                </div>
            );
        }

        ReactDOM.render(<App />, document.getElementById('react-root'));
    </script>
    @endverbatim
@endpush