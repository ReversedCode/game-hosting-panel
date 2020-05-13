import React, {useEffect, useState} from 'react';
import Card from "../ui/Card";
import Game from "./../sections/partials/Game";
import useGames from "../hooks/useGames";
import {useDispatch} from "react-redux";

export default function GameSelection({onSelect}) {
    const [selected, setSelected] = useState(null);
    const dispatch = useDispatch();
    const games = useGames();

    useEffect(() => {
        dispatch.games.load();
    }, []);

    function handleOnClick(id) {
        let n = selected === id ? null : id;
        setSelected(n);
        onSelect && onSelect(n);
    }

    return <Card
        title="Game"
        subtitle="Which game do you want to host"
        loading={games.loading}
        cols={5}
    >
        {Object.entries(games.games).map(([id, game]) => (
            <Game
                key={id}
                id={id}
                game={game}
                selected={selected ? id === selected : null}
                onClick={handleOnClick}
            />
        ))}
    </Card>
}