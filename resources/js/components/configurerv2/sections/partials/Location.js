import React from 'react';
import feather from "feather-icons";

export default function Location({id, flag, location, selected, onClick}) {
    const {name, enabled} = location;

    return <div
        onClick={enabled ? onClick.bind(this, id) : undefined}
        className={`trans relative flex flex-row items-center
            px-6 py-4 border ${selected ? 'border-blue-600 bg-blue-100 shadow' : ''}
            rounded ${enabled ? 'cursor-pointer' : 'opacity-50 cursor-not-allowed'}
        `}
        style={{filter: enabled ? '' : 'grayscale(100%)'}}
    >
        {/* Selection check */}
        <div
            className={`trans m-2 absolute text-white top-0 right-0 bg-blue-600 ${selected ? 'opacity-100' : 'opacity-0'} rounded-full shadow`}
            dangerouslySetInnerHTML={{__html: feather.icons.check.toSvg()}}
        />

        {/* Location flag */}
        <div className="w-16 h-16">
            <img alt={name} src={flag}/>
        </div>

        {/* Location name and description */}
        <div className={`ml-4 flex-grow ${selected ? 'text-blue-700' : 'text-black'} text-center text-xl font-bold select-none`}>{name}</div>
    </div>
}