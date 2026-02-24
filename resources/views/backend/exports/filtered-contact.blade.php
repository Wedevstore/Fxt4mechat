<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            {{-- <th>Campaign</th> --}}
            {{-- <th>Requirement Details</th> --}}

        </tr>
    </thead>
    <tbody>
        @foreach($contacts as $key => $contact)
        <tr>
            <td>{{ $key+1 }}</td>
            <td>{{ $contact->name }}</td>
            <td>{{ $contact->phone }}</td>
            <td>{{ $contact->email }}</td>
            {{-- <td>{{ $contact->campaign_name }}</td> --}}
            {{-- <td>{{ $contact->details_requirment }}</td> --}}
         </tr>
        @endforeach
    </tbody>
</table>