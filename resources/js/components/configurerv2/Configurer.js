import React from 'react';
import Title from "./Title";
import GameSelection from "./sections/GameSelection";
import LocationSelection from "./sections/LocationSelection";
import ResourceSelection from "./sections/ResourceSelection";
import Summary from "./sections/partials/Summary";
import {useDispatch} from "react-redux";

export default function Configurer({handleChange, cost}) {
    const dispatch = useDispatch();

    function handleGameSelect(game) {
        dispatch.specs.update({game});
        if (game) dispatch.locations.load();
    }

    function handleLocationSelect(location) {
        dispatch.specs.update({location})
    }

    function handleResourceSelect(resource) {
        dispatch.specs.update({...resource});
    }

    function handlePeriodSelect(period) {
        dispatch.specs.update({period});
    }

    return <>
        <Title/>
        <GameSelection onSelect={handleGameSelect}/>
        <LocationSelection onSelect={handleLocationSelect}/>
        <ResourceSelection onSelect={handleResourceSelect}/>
        <Summary onPeriodSelect={handlePeriodSelect}/>
    </>
}